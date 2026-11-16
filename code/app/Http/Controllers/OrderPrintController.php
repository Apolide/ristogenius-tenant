<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;

class OrderPrintController extends Controller
{
    public function __invoke(Order $order)
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        $order->load(['courses.items.modifiers', 'courses.items.ticket', 'customer']);

        return view('orders.print', compact('order'));
    }
}
