<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;

class CustomerOrderPrintController extends Controller
{
    public function __invoke(Order $order)
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
        abort_unless(in_array($order->fulfillment, ['pickup', 'delivery']), 404);
        $order->load(['courses.items.modifiers', 'courses.items.ticket', 'customer']);

        return view('orders.print', compact('order'));
    }
}
