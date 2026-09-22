<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Contracts\WhatsappGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly WhatsappGateway $whatsapp)
    {
    }

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

        // Selalu hitung total sesungguhnya (TIDAK ikut filter aktif) - biar admin
        // selalu lihat ada berapa pesanan tamu baru yg masuk, apa pun filter yg dipakai.
        $pendingConfirmationCount = Order::query()->where('status', 'pending_confirmation')->count();

        return view('orders.index', [
            'orders' => $orders,
            'from' => $filters['from'],
            'to' => $filters['to'],
            'paymentMethod' => $filters['paymentMethod'],
            'status' => $filters['status'],
            'search' => $filters['search'],
            'paymentMethods' => $paymentMethods,
            'pendingConfirmationCount' => $pendingConfirmationCount,
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

    /**
     * Konfirmasi pesanan tamu (self-order) - kalau diantar, ongkir diisi manual
     * di sini (bukan hitung otomatis). Total dihitung ulang, lalu WA ke customer.
     */
    public function confirm(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending_confirmation') {
            return back()->with('error', 'Order ini sudah diproses, tidak bisa dikonfirmasi lagi.');
        }

        $validated = $request->validate([
            'delivery_fee' => ['nullable', 'integer', 'min:0'],
        ]);

        $deliveryFee = $order->fulfillment_method === 'delivery' ? (int) ($validated['delivery_fee'] ?? 0) : 0;

        $order->update([
            'delivery_fee' => $deliveryFee,
            'total' => (int) $order->subtotal + $deliveryFee,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->notifyCustomerConfirmed($order->fresh());

        return back()->with('status', "Order {$order->order_number} dikonfirmasi & customer sudah dikabari via WA.");
    }

    /** Pesanan tamu yang sudah dikonfirmasi, ditandai selesai (sudah diambil/diantar). */
    public function complete(Order $order): RedirectResponse
    {
        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Order ini belum dikonfirmasi.');
        }

        $order->update(['status' => 'completed']);

        return back()->with('status', "Order {$order->order_number} ditandai selesai.");
    }

    /** Tolak/batalkan pesanan tamu (belum bayar apa pun, jadi cukup diubah statusnya). */
    public function reject(Order $order): RedirectResponse
    {
        if (! in_array($order->status, ['pending_confirmation', 'confirmed'], true)) {
            return back()->with('error', 'Order ini tidak bisa dibatalkan dari sini.');
        }

        $order->update(['status' => 'voided']);

        return back()->with('status', "Order {$order->order_number} dibatalkan.");
    }

    private function notifyCustomerConfirmed(Order $order): void
    {
        $phone = trim((string) $order->customer_phone);
        if ($phone === '') {
            return;
        }

        $fulfillment = $order->fulfillment_method === 'delivery'
            ? 'Pesananmu akan diantar ke: '.$order->delivery_address
            : 'Pesananmu bisa diambil di kedai.';

        $ongkirLine = $order->delivery_fee > 0
            ? "\nOngkir: Rp".number_format((int) $order->delivery_fee, 0, ',', '.')
            : '';

        $message = "🎉 Pesanan *{$order->order_number}* sudah *dikonfirmasi*!\n\n"
            ."Subtotal: Rp".number_format((int) $order->subtotal, 0, ',', '.')
            .$ongkirLine
            ."\n*Total: Rp".number_format((int) $order->total, 0, ',', '.')."*\n\n"
            .$fulfillment."\n\nTerima kasih sudah pesan! ☕";

        try {
            $this->whatsapp->sendMessage($phone, $message);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
