<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Order::query()->orderByDesc('mobile_created_at');
        if ($from) {
            $query->whereDate('mobile_created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('mobile_created_at', '<=', $to);
        }

        $orders = $query->paginate(25)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'from' => $from,
            'to' => $to,
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
