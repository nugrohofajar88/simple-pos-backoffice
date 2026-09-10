<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->sync->pullOrders($request->query('since'));

        return response()->json([
            'data' => [
                'orders' => $orders->map(fn (Order $o) => [
                    'id' => $o->id,
                    'orderNumber' => $o->order_number,
                    'status' => $o->status,
                    'customerName' => $o->customer_name,
                    'subtotal' => $o->subtotal,
                    'total' => $o->total,
                    'paymentMethod' => $o->payment_method,
                    'note' => $o->note,
                    'createdAt' => $o->mobile_created_at->toIso8601String(),
                    'items' => $o->items->map(fn (OrderItem $i) => [
                        'productRemoteId' => $i->product_id,
                        'productName' => $i->product_name,
                        'unitPrice' => $i->unit_price,
                        'costPrice' => $i->cost_price,
                        'qty' => $i->qty,
                        'note' => $i->note,
                        'printed' => $i->printed,
                        'createdAt' => $i->mobile_created_at->toIso8601String(),
                        'modifiers' => $i->modifiers->map(fn (OrderItemModifier $m) => [
                            'modifierGroupName' => $m->modifier_group_name,
                            'modifierOptionName' => $m->modifier_option_name,
                            'priceDelta' => $m->price_delta,
                        ]),
                    ]),
                ]),
            ],
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $orders = $request->validate(['orders' => ['required', 'array']])['orders'];

        return response()->json(['results' => $this->sync->pushOrders($orders)]);
    }

    /**
     * Order dihapus dari mobile (fitur "Hapus Order" - order tetap immutable, gak ada edit,
     * cuma hapus lalu buat ulang). Route-model-binding otomatis 404 kalau order-nya udah gak
     * ada - mobile treat 404 sbg "sukses" juga (tujuan akhirnya sama: gak ada lagi di server).
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(['deleted' => true]);
    }
}
