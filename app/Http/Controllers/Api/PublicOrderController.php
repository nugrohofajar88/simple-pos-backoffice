<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Contracts\WhatsappGateway;
use App\Support\OrderCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Endpoint PUBLIK (tanpa auth:sanctum) khusus utk situs self-order pelanggan
 * (pesenkopi) - jangan taruh apa pun di sini yg butuh hak admin. Dibatasi
 * throttle ketat di routes/api.php krn siapa saja bisa akses tanpa token.
 */
class PublicOrderController extends Controller
{
    public function __construct(private readonly OrderCreationService $orders)
    {
    }

    /** Menu aktif, nested (kategori->produk->modifier). cost_price SENGAJA tidak diikutkan (data internal). */
    public function menu(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['products' => fn ($q) => $q->where('is_active', true)
                ->orderBy('sort_order')
                ->with(['modifierGroups' => fn ($q2) => $q2->orderBy('sort_order')->with([
                    'options' => fn ($q3) => $q3->orderBy('sort_order'),
                ])])])
            ->get();

        return response()->json([
            'data' => [
                'categories' => $categories->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'products' => $c->products->map(fn (Product $p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'basePrice' => (int) $p->base_price,
                        'imageUrl' => $p->image_url,
                        'modifierGroups' => $p->modifierGroups->map(fn (ModifierGroup $g) => [
                            'id' => $g->id,
                            'name' => $g->name,
                            'selectionType' => $g->selection_type,
                            'isRequired' => (bool) $g->is_required,
                            'options' => $g->options->map(fn (ModifierOption $o) => [
                                'id' => $o->id,
                                'name' => $o->name,
                                'priceDelta' => (int) $o->price_delta,
                                'isDefault' => (bool) $o->is_default,
                            ]),
                        ]),
                    ]),
                ]),
            ],
        ]);
    }

    /** Order tamu (self-order) - status awal SELALU pending_confirmation, ongkir ditentukan admin belakangan. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'fulfillment_method' => ['required', 'string', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:fulfillment_method,delivery', 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.note' => ['nullable', 'string', 'max:200'],
            'items.*.modifierOptionIds' => ['nullable', 'array'],
            'items.*.modifierOptionIds.*' => ['integer'],
        ]);

        $order = $this->orders->create([
            'source' => 'app',
            'status' => 'pending_confirmation',
            'paymentMethod' => 'Belum Ditentukan',
            'customerName' => $validated['customer_name'],
            'customerPhone' => $validated['customer_phone'],
            'fulfillmentMethod' => $validated['fulfillment_method'],
            'deliveryAddress' => $validated['delivery_address'] ?? null,
            'note' => $validated['note'] ?? null,
            'items' => $validated['items'],
        ]);

        $this->notifyAdminOfNewOrder($order);

        return response()->json([
            'data' => [
                'orderNumber' => $order->order_number,
                'total' => (int) $order->total,
            ],
            'message' => 'Pesanan diterima, menunggu konfirmasi admin.',
        ], 201);
    }

    private function notifyAdminOfNewOrder(\App\Models\Order $order): void
    {
        $adminPhone = trim((string) Setting::getValue('admin_whatsapp', ''));
        if ($adminPhone === '') {
            return;
        }

        $lines = $order->items->map(
            fn ($i) => "• {$i->qty}x {$i->product_name}"
        )->implode("\n");

        $fulfillment = $order->fulfillment_method === 'delivery'
            ? "Diantar ke: {$order->delivery_address}"
            : 'Ambil sendiri';

        $message = "☕ *Pesanan Baru Masuk!*\n\n"
            ."No. Pesanan: *{$order->order_number}*\n"
            ."Nama: {$order->customer_name}\n"
            ."No. WA: {$order->customer_phone}\n"
            ."{$fulfillment}\n\n"
            ."{$lines}\n\n"
            ."Subtotal: Rp".number_format((int) $order->subtotal, 0, ',', '.')
            .($order->note ? "\nCatatan: {$order->note}" : '')
            ."\n\nSilakan konfirmasi pesanan ini dari panel admin.";

        try {
            app(WhatsappGateway::class)->sendMessage($adminPhone, $message);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
