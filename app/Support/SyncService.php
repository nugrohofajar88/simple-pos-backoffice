<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SyncService
{
    /**
     * Snapshot penuh menu aktif (mobile selalu replace-total, bukan delta).
     */
    public function pullMenu(): array
    {
        return [
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'products' => Product::query()->orderBy('sort_order')->get(),
            'modifierGroups' => ModifierGroup::query()->orderBy('sort_order')->get(),
            'modifierOptions' => ModifierOption::query()->orderBy('sort_order')->get(),
        ];
    }

    public function pushOrders(array $orders): array
    {
        $results = [];

        DB::transaction(function () use ($orders, &$results) {
            foreach ($orders as $orderData) {
                $existing = Order::query()->where('order_number', $orderData['orderNumber'])->first();
                if ($existing) {
                    $results[] = ['localId' => $orderData['localId'] ?? null, 'remoteId' => $existing->id];
                    continue;
                }

                $order = Order::query()->create([
                    'order_number' => $orderData['orderNumber'],
                    'status' => $orderData['status'] ?? 'completed',
                    'customer_name' => $orderData['customerName'] ?? null,
                    'subtotal' => $orderData['subtotal'],
                    'total' => $orderData['total'],
                    'payment_method' => $orderData['paymentMethod'],
                    'note' => $orderData['note'] ?? null,
                    'mobile_created_at' => $orderData['createdAt'],
                ]);

                foreach ($orderData['items'] ?? [] as $itemData) {
                    // Produk yg direferensikan mobile mungkin udah gak ada lagi di server (mis.
                    // sehabis reset data) - product_id nullable (ON DELETE SET NULL), jadi kalau
                    // gak ketemu jangan gagalkan seluruh push order-nya, null-kan aja referensinya.
                    $productId = $itemData['productRemoteId'] ?? null;
                    if ($productId && ! Product::query()->withTrashed()->whereKey($productId)->exists()) {
                        $productId = null;
                    }

                    $item = OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'product_name' => $itemData['productName'],
                        'unit_price' => $itemData['unitPrice'],
                        'cost_price' => $itemData['costPrice'] ?? 0,
                        'qty' => $itemData['qty'],
                        'note' => $itemData['note'] ?? null,
                        'printed' => $itemData['printed'] ?? false,
                        'mobile_created_at' => $itemData['createdAt'] ?? $orderData['createdAt'],
                    ]);

                    foreach ($itemData['modifiers'] ?? [] as $modifierData) {
                        OrderItemModifier::query()->create([
                            'order_item_id' => $item->id,
                            'modifier_group_name' => $modifierData['modifierGroupName'],
                            'modifier_option_name' => $modifierData['modifierOptionName'],
                            'price_delta' => $modifierData['priceDelta'] ?? 0,
                        ]);
                    }
                }

                $results[] = ['localId' => $orderData['localId'] ?? null, 'remoteId' => $order->id];
            }
        });

        return $results;
    }

    /**
     * Order beserta item+modifier yg masuk ke server SEJAK $since (cursor = created_at server,
     * BUKAN mobile_created_at - order yg dibuat offline lalu baru dipush belakangan tetap harus
     * kepull walau mobile_created_at-nya lebih lama dari cursor terakhir).
     */
    public function pullOrders(?string $since): \Illuminate\Support\Collection
    {
        $query = Order::query()->with('items.modifiers')->orderBy('created_at');
        if ($since) {
            $query->where('created_at', '>=', Carbon::parse($since));
        }

        return $query->get();
    }

    public function pullExpenses(?string $since): \Illuminate\Support\Collection
    {
        $query = Expense::query()->orderBy('created_at');
        if ($since) {
            $query->where('created_at', '>=', Carbon::parse($since));
        }

        return $query->get();
    }

    public function pushExpenses(array $expenses): array
    {
        $results = [];

        foreach ($expenses as $expenseData) {
            $expense = Expense::query()->create([
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'mobile_created_at' => $expenseData['createdAt'] ?? null,
            ]);

            $results[] = ['localId' => $expenseData['localId'] ?? null, 'remoteId' => $expense->id];
        }

        return $results;
    }

    public function pushSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::setValue($key, (string) $value);
        }
    }
}
