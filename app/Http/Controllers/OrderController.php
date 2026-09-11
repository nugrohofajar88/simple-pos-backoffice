<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'paymentMethod' => $request->query('payment_method'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
        ];

        $applyFilters = function ($query) use ($filters) {
            if ($filters['from']) {
                $query->whereDate('mobile_created_at', '>=', $filters['from']);
            }
            if ($filters['to']) {
                $query->whereDate('mobile_created_at', '<=', $filters['to']);
            }
            if ($filters['paymentMethod']) {
                $query->where('payment_method', $filters['paymentMethod']);
            }
            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }
            if ($filters['search']) {
                $query->where(fn ($q) => $q->where('order_number', 'like', "%{$filters['search']}%")
                    ->orWhere('customer_name', 'like', "%{$filters['search']}%"));
            }

            return $query;
        };

        $orders = $applyFilters(Order::query())
            ->with('items')
            ->orderByDesc('mobile_created_at')
            ->paginate(25)
            ->withQueryString();

        $orderCount = $applyFilters(Order::query())->count();
        $totalSales = (int) $applyFilters(Order::query())->sum('total');
        $totalItems = (int) OrderItem::query()->whereHas('order', $applyFilters)->sum('qty');

        $paymentMethods = Order::query()->select('payment_method')->distinct()->orderBy('payment_method')->pluck('payment_method');

        return view('orders.index', [
            'orders' => $orders,
            'from' => $filters['from'],
            'to' => $filters['to'],
            'paymentMethod' => $filters['paymentMethod'],
            'status' => $filters['status'],
            'search' => $filters['search'],
            'paymentMethods' => $paymentMethods,
            'stats' => [
                'orderCount' => $orderCount,
                'totalSales' => $totalSales,
                'totalItems' => $totalItems,
                'avgOrderValue' => $orderCount > 0 ? (int) round($totalSales / $orderCount) : 0,
            ],
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
