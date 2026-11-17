<?php

namespace App\Services\Orders;

use App\Models\Booking;
use App\Models\Order;
use App\Models\RoomTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TableOrderService
{
    public function open(string $tableId, int $covers = 1, ?string $bookingId = null, bool $staff = true): Order
    {
        Validator::make(compact('covers'), ['covers' => 'required|integer|min:1|max:1000'])->validate();

        return DB::transaction(function () use ($tableId, $covers, $bookingId, $staff) {
            $table = RoomTable::with('room')->lockForUpdate()->findOrFail($tableId);
            $this->check($table->room->active, __('La sala non è attiva.'));
            $existing = DB::table('order_tables')->where('active_table_id', $tableId)->lockForUpdate()->value('order_id');
            if ($existing) {
                return Order::findOrFail($existing);
            }
            $booking = $bookingId ? Booking::with('tables')->findOrFail($bookingId) : null;
            if ($booking) {
                $this->check($booking->booking_date->isToday() && $booking->tables->contains('id', $tableId) && in_array($booking->status, ['accepted', 'seated']), __('Prenotazione non associabile al tavolo.'));
            }
            $order = Order::create([
                'status' => 'open', 'fulfillment' => 'dine_in', 'delivery_fee_cents' => 0, 'service_date' => now()->toDateString(), 'created_by' => $staff ? auth()->id() : null, 'source' => $staff ? 'staff' : 'customer',
                'table_name' => $table->name, 'room_name' => $table->room->name,
                'covers' => $booking?->pax ?? $covers, 'cover_price_cents' => OrderService::cents((string) $table->room->service_charge),
                'booking_id' => $booking?->id, 'customer_id' => $booking?->customer_id,
            ]);
            DB::table('order_tables')->insert(['order_id' => $order->id, 'room_table_id' => $tableId, 'active_table_id' => $tableId, 'created_at' => now(), 'updated_at' => now()]);
            $order->courses()->create(['number' => 1]);
            $this->event($order, 'opened');

            return $order;
        }, 3);
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }

    private function event(Order $order, string $action): void
    {
        $order->events()->create(['user_id' => $order->created_by, 'action' => $action]);
    }
}
