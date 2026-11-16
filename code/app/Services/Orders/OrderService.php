<?php

namespace App\Services\Orders;

use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\OrderItem;
use App\Models\ProductionTicket;
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

        return app(TableOrderService::class)->open($tableId, $covers, $bookingId);
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
                    [$price, $modifiers] = app(OrderPricingService::class)->modifiers($product, $item->base_price_cents, $added, $removed, $item);
                    $item->update(['unit_price_cents' => $price]);
                    $item->modifiers()->delete();
                    $item->modifiers()->createMany($modifiers);
                }
                $item->update(['quantity' => $quantity, 'notes' => $notes]);
                $this->event($order, 'item_updated', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $quantity, 'notes' => $notes, 'before' => $before, 'after' => $item->fresh('modifiers')->toArray()]);

                return;
            }
            $this->check(! $course->sent_at && ! $course->submission_key, 'Aggiungi i prodotti a una nuova uscita del personale.');
            $quote = app(OrderPricingService::class)->quote([
                'category_id' => $categoryId, 'product_id' => $productId, 'quantity' => $quantity, 'notes' => $notes, 'added' => $added, 'removed' => $removed,
            ]);
            $item = app(OrderPricingService::class)->append($course, $quote);
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
            app(ProductionService::class)->send($course);
            $this->event($order, 'course_sent', ['course' => $course->number]);
            if ($course->customer_id) {
                app(\App\Services\PublicOrders\OrderNotifications::class)->courseAccepted($course);
            } else {
                app(\App\Services\Notification\NotificationService::class)->forPermission(
                    Permissions::ORDERS, 'staff_order.sent', 'Comanda inviata dal personale',
                    'Tavolo '.$order->tables->pluck('name')->implode(', ').' · Uscita '.$course->number.' inviata ai reparti.', route('orders.show', $order)
                );
            }
        });
    }

    public function rejectCourse(string $orderId, string $courseId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $courseId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->findOrFail($courseId);
            $this->check($course->submission_key && ! $course->sent_at && ! $course->rejected_at, 'Questa uscita non può essere rifiutata.');
            $course->update(['rejected_at' => now()]);
            $this->event($order, 'course_rejected', ['course' => $course->number]);
            app(\App\Services\PublicOrders\OrderNotifications::class)->courseRejected($course);
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
            $this->check(! $order->pendingProposal()->exists(), 'Attendi la risposta del cliente prima di incassare.');
            $this->check($order->fulfillment === 'dine_in' || ! in_array($order->fulfillment_status, ['received', 'rejected', 'cancelled']), 'L’ordine deve essere accettato prima di incassare.');
            $this->check($order->fulfillment !== 'dine_in' || ! $order->courses()->whereNull('sent_at')->whereNull('rejected_at')->whereHas('items')->exists(), 'Invia o rimuovi le righe ancora in bozza prima di incassare.');
            $this->check($order->totalCents() === $expectedTotal, 'Il totale è cambiato. Verifica il nuovo importo prima di confermare.');
            $order->payments()->create(['user_id' => auth()->id(), 'amount_cents' => $expectedTotal, 'method' => $method]);
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
            $this->event($order, 'paid', ['amount_cents' => $expectedTotal, 'method' => $method]);
            if ($order->customer_email) {
                app(\App\Services\PublicOrders\OrderNotifications::class)->status($order, 'paid');
            }
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
            if ($order->fulfillment !== 'dine_in') {
                $order->update(['fulfillment_status' => 'cancelled']);
                $order->proposals()->where('status', 'pending')->update(['status' => 'withdrawn', 'responded_at' => now()]);
                app(\App\Services\PublicOrders\OrderNotifications::class)->status($order, 'cancelled');
            }
            $this->event($order, 'cancelled');
        });
    }

    private function lockedOpen(string $id): Order
    {
        $order = Order::lockForUpdate()->findOrFail($id);
        $this->check($order->status === 'open', 'La comanda è chiusa.');

        return $order;
    }

    private function editable(OrderCourse $course): void
    {
        $this->check(! $course->rejected_at, 'Questa uscita è stata rifiutata.');
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
