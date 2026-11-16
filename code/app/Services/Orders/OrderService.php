<?php

namespace App\Services\Orders;

use App\Models\Booking;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\OrderItem;
use App\Models\ProductionTicket;
use App\Models\RoomTable;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function authorize(string $permission = Permissions::ORDERS): void
    {
        abort_unless(auth()->user() && app(Permissions::class)->can(auth()->user(), $permission), 403);
    }

    public static function cents(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $parts = explode('.', ltrim($amount, '-'));
        $value = (int) $parts[0] * 100 + (int) str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');

        return $negative ? -$value : $value;
    }

    public function open(string $tableId, int $covers = 1, ?string $bookingId = null): Order
    {
        $this->authorize();
        Validator::make(compact('covers'), ['covers' => 'required|integer|min:1|max:1000'])->validate();

        return DB::transaction(function () use ($tableId, $covers, $bookingId) {
            $table = RoomTable::with('room')->lockForUpdate()->findOrFail($tableId);
            $this->check($table->room->active, 'La sala non è attiva.');
            $existing = DB::table('order_tables')->where('active_table_id', $tableId)->lockForUpdate()->value('order_id');
            if ($existing) {
                return Order::findOrFail($existing);
            }
            $booking = $bookingId ? Booking::with('tables')->findOrFail($bookingId) : null;
            if ($booking) {
                $this->check($booking->booking_date->isToday() && $booking->tables->contains('id', $tableId) && in_array($booking->status, ['accepted', 'seated']), 'Prenotazione non associabile al tavolo.');
            }
            $order = Order::create([
                'service_date' => now()->toDateString(), 'created_by' => auth()->id(),
                'table_name' => $table->name, 'room_name' => $table->room->name,
                'covers' => $booking?->pax ?? $covers, 'cover_price_cents' => self::cents((string) $table->room->service_charge),
                'booking_id' => $booking?->id, 'customer_id' => $booking?->customer_id,
            ]);
            DB::table('order_tables')->insert(['order_id' => $order->id, 'room_table_id' => $tableId, 'active_table_id' => $tableId, 'created_at' => now(), 'updated_at' => now()]);
            $order->courses()->create(['number' => 1]);
            $this->event($order, 'opened');

            return $order;
        }, 3);
    }

    public function addCourse(string $orderId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->create(['number' => (int) $order->courses()->max('number') + 1]);
            $this->event($order, 'course_created', ['course' => $course->number]);
        });
    }

    public function covers(string $orderId, int $covers): void
    {
        $this->authorize();
        Validator::make(compact('covers'), ['covers' => 'required|integer|min:1|max:1000'])->validate();
        DB::transaction(function () use ($orderId, $covers) {
            $order = $this->lockedOpen($orderId);
            $order->update(['covers' => $covers]);
            $this->event($order, 'covers_changed', compact('covers'));
        });
    }

    public function saveItem(string $orderId, string $courseId, string $categoryId, string $productId, int $quantity, string $notes = '', array $added = [], array $removed = [], ?string $itemId = null): void
    {
        $this->authorize();
        Validator::make(compact('quantity', 'notes', 'added', 'removed'), [
            'quantity' => 'required|integer|min:1|max:1000', 'notes' => 'string|max:2000',
            'added' => 'array|max:100', 'removed' => 'array|max:100', 'added.*' => 'uuid|distinct', 'removed.*' => 'uuid|distinct',
        ])->validate();
        DB::transaction(function () use ($orderId, $courseId, $categoryId, $productId, $quantity, $notes, $added, $removed, $itemId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->findOrFail($courseId);
            $this->editable($course);
            // Sent rows retain all original prices, tax and routing when notes/quantity change.
            if ($itemId) {
                $item = $course->items()->findOrFail($itemId);
                $before = $item->load('modifiers')->toArray();
                if ($item->product_id) {
                    $product = DigitalMenuProduct::with('ingredients')->findOrFail($item->product_id);
                    [$price, $modifiers] = $this->modifiers($product, $item->base_price_cents, $added, $removed, $item);
                    $item->update(['unit_price_cents' => $price]);
                    $item->modifiers()->delete();
                    $item->modifiers()->createMany($modifiers);
                }
                $item->update(['quantity' => $quantity, 'notes' => $notes]);
                $this->event($order, 'item_updated', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $quantity, 'notes' => $notes, 'before' => $before, 'after' => $item->fresh('modifiers')->toArray()]);

                return;
            }
            $this->check(! $course->sent_at, 'Aggiungi i prodotti a una nuova uscita.');
            $category = DigitalMenuCategory::with('menu')->findOrFail($categoryId);
            $this->check($category->is_enabled && $category->menu->is_visible, 'Menu o categoria non disponibile.');
            $product = $category->products()->with(['ingredients', 'department'])->where('digital_menu_products.id', $productId)->firstOrFail();
            $this->check($product->is_active && $product->pivot->is_visible, 'Prodotto non disponibile.');
            $base = self::cents((string) ($product->pivot->menu_price ?? $product->price));
            [$price, $modifiers] = $this->modifiers($product, $base, $added, $removed);
            $item = $course->items()->create([
                'product_id' => $product->id, 'menu_id' => $category->menu->id,
                'product_name' => $product->translatedName(), 'menu_name' => $category->menu->translatedName(),
                'department_id' => $product->department_id, 'department_name' => $product->department?->name,
                'requires_production' => (bool) $product->department?->production,
                'quantity' => $quantity, 'base_price_cents' => $base, 'unit_price_cents' => $price, 'vat' => $product->vat, 'notes' => $notes,
            ]);
            $item->modifiers()->createMany($modifiers);
            $this->event($order, 'item_added', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $quantity, 'snapshot' => $item->load('modifiers')->toArray()]);
        });
    }

    public function removeItem(string $orderId, string $itemId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $itemId) {
            $order = $this->lockedOpen($orderId);
            $item = $order->items()->findOrFail($itemId);
            $this->editable($item->course);
            $this->event($order, 'item_removed', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $item->quantity, 'unit_price_cents' => $item->unit_price_cents, 'snapshot' => $item->load('modifiers')->toArray()]);
            $ticketId = $item->production_ticket_id;
            $item->delete();
            if ($ticketId && ! OrderItem::where('production_ticket_id', $ticketId)->exists()) {
                ProductionTicket::whereKey($ticketId)->update(['status' => 'cancelled']);
            }
        });
    }

    public function send(string $orderId, string $courseId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $courseId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->with('items')->findOrFail($courseId);
            if ($course->sent_at) {
                return;
            }
            $this->check($course->items->isNotEmpty(), 'L’uscita è vuota.');
            foreach ($course->items->where('requires_production', true)->groupBy('department_id') as $department => $items) {
                $this->check($department !== '' && \App\Models\Department::whereKey($department)->where('production', true)->exists(), 'Reparto produttivo non più disponibile.');
                $ticket = $course->tickets()->create(['department_id' => $department, 'department_name' => $items->first()->department_name, 'sent_at' => now()]);
                $course->items()->whereIn('id', $items->pluck('id'))->update(['production_ticket_id' => $ticket->id]);
            }
            $course->update(['sent_at' => now()]);
            $this->event($order, 'course_sent', ['course' => $course->number]);
        });
    }

    public function advance(string $ticketId, string $expectedStatus): void
    {
        $this->authorize(Permissions::KDS);
        DB::transaction(function () use ($ticketId, $expectedStatus) {
            $reference = ProductionTicket::with('course')->findOrFail($ticketId);
            $order = Order::lockForUpdate()->findOrFail($reference->course->order_id);
            $ticket = ProductionTicket::lockForUpdate()->findOrFail($ticketId);
            $this->check($order->status !== 'cancelled', 'Ordine annullato.');
            $this->check($ticket->status === $expectedStatus, 'Stato già aggiornato: aggiorna la schermata.');
            $next = ['queued' => ['preparing', 'started_at'], 'preparing' => ['ready', 'ready_at'], 'ready' => ['served', 'served_at']][$ticket->status] ?? null;
            $this->check($next !== null, 'Transizione non consentita.');
            $ticket->update(['status' => $next[0], $next[1] => now()]);
            $this->event($order, 'production_advanced', ['ticket' => $ticketId, 'status' => $next[0]]);
        });
    }

    public function pay(string $orderId, string $method, int $expectedTotal): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('method'), ['method' => 'required|in:manual,cash,card,voucher'])->validate();
        DB::transaction(function () use ($orderId, $method, $expectedTotal) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            if ($order->status === 'paid') {
                return;
            }
            $this->check($order->status === 'open', 'Ordine non aperto.');
            $this->check(! $order->courses()->whereNull('sent_at')->whereHas('items')->exists(), 'Invia o rimuovi le righe ancora in bozza prima di incassare.');
            $this->check($order->totalCents() === $expectedTotal, 'Il totale è cambiato. Verifica il nuovo importo prima di confermare.');
            $order->payments()->create(['user_id' => auth()->id(), 'amount_cents' => $expectedTotal, 'method' => $method]);
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
            $this->event($order, 'paid', ['amount_cents' => $expectedTotal, 'method' => $method]);
        });
    }

    public function cancel(string $orderId): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId) {
            $order = $this->lockedOpen($orderId);
            $this->check(! ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $orderId))->whereNotIn('status', ['queued', 'cancelled'])->exists(), 'Una uscita è già in lavorazione: impossibile annullare.');
            ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $orderId))->update(['status' => 'cancelled']);
            $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
            $this->event($order, 'cancelled');
        });
    }

    private function modifiers(DigitalMenuProduct $product, int $base, array $added, array $removed, ?OrderItem $item = null): array
    {
        $this->check(! array_intersect($added, $removed), 'Un ingrediente non può essere aggiunto e rimosso insieme.');
        $price = $base;
        $modifiers = [];
        $snapshots = $item?->modifiers()->get() ?? collect();
        foreach ($snapshots->whereNull('ingredient_id') as $snapshot) {
            $price += $snapshot->price_cents;
            $modifiers[] = $snapshot->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']);
        }
        foreach (['add' => $added, 'remove' => $removed] as $action => $ids) {
            foreach ($ids as $id) {
                $snapshot = $snapshots->first(fn ($m) => $m->ingredient_id === $id && $m->action === $action);
                if ($snapshot) {
                    $price += $snapshot->price_cents;
                    $modifiers[] = $snapshot->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']);

                    continue;
                }
                $ingredient = DigitalMenuIngredient::findOrFail($id);
                $this->check($action !== 'remove' || $product->ingredients->contains('id', $id), 'Ingrediente non presente nella ricetta.');
                $delta = self::cents((string) ($action === 'add' ? $ingredient->add_price : $ingredient->remove_price));
                $price += $delta;
                $modifiers[] = ['ingredient_id' => $id, 'ingredient_name' => $ingredient->translatedName(), 'action' => $action, 'price_cents' => $delta];
            }
        }
        $this->check($price >= 0, 'Il prezzo non può essere negativo.');

        return [$price, $modifiers];
    }

    private function lockedOpen(string $id): Order
    {
        $order = Order::lockForUpdate()->findOrFail($id);
        $this->check($order->status === 'open', 'La comanda è chiusa.');

        return $order;
    }

    private function editable(OrderCourse $course): void
    {
        $this->check(! $course->tickets()->whereNotIn('status', ['queued', 'cancelled'])->exists(), 'Uscita già in lavorazione: modifiche e cancellazioni bloccate.');
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }

    private function event(Order $order, string $action, array $details = []): void
    {
        $order->events()->create(['user_id' => auth()->id(), 'action' => $action, 'details' => $details]);
    }
}
