<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()->orderByDesc('mobile_created_at')->paginate(25);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.modifiers']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }
}
