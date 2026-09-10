<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Order gak bisa diedit (immutable, sesuai desain sistem) - kalau ada kesalahan,
     * dihapus dari sini lalu dibuat ulang dari mobile. Cascade hapus items+modifiers
     * (FK cascadeOnDelete). Ini CUMA menghapus catatan di web/laporan - order lokal
     * di HP yang bikin order ini TIDAK ikut terhapus (gak ada jalur sync balik).
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('orders.index')->with('status', 'Order dihapus.');
    }
}
