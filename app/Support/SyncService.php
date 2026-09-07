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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SyncService
{
    /**
     * Upsert 1 baris hasil push dari mobile utk model yg support 2 arah sync (Last-Write-Wins).
     * Balikin [remoteId, updatedAt] utk dikirim balik ke mobile.
     */
    public function upsertSyncable(string $modelClass, array $item, array $fields): array
    {
        /** @var Model $model */
        $remoteId = $item['remoteId'] ?? null;
        $incomingUpdatedAt = isset($item['updatedAt']) ? Carbon::parse($item['updatedAt']) : now();
        $isDeleted = ! empty($item['deletedAt']);

        $existing = $remoteId ? $modelClass::withTrashed()->find($remoteId) : null;

        if (! $existing) {
            if ($isDeleted) {
                // Baris baru yg langsung dihapus sebelum sempat sync - abaikan saja.
                return ['remoteId' => null, 'updatedAt' => $incomingUpdatedAt->toIso8601String()];
            }

            $data = array_intersect_key($item, array_flip($fields));
            $model = $modelClass::query()->create($data);
            $model->forceFill(['updated_at' => $incomingUpdatedAt])->save();

            return ['remoteId' => $model->id, 'updatedAt' => $model->updated_at->toIso8601String()];
        }

        // Last-Write-Wins: server cuma nerima perubahan kalau versi mobile lebih baru.
        if ($existing->updated_at && $incomingUpdatedAt->lessThanOrEqualTo($existing->updated_at)) {
            return [
                'remoteId' => $existing->id,
                'updatedAt' => $existing->updated_at->toIso8601String(),
                'deletedAt' => $existing->deleted_at?->toIso8601String(),
            ];
        }

        if ($isDeleted) {
            $existing->forceFill(['updated_at' => $incomingUpdatedAt])->save();
            $existing->delete();

            return [
                'remoteId' => $existing->id,
                'updatedAt' => $incomingUpdatedAt->toIso8601String(),
                'deletedAt' => $existing->fresh()?->deleted_at?->toIso8601String(),
            ];
        }

        if ($existing->trashed()) {
            $existing->restore();
        }

        $data = array_intersect_key($item, array_flip($fields));
        $existing->update($data);
        $existing->forceFill(['updated_at' => $incomingUpdatedAt])->save();

        return ['remoteId' => $existing->id, 'updatedAt' => $existing->updated_at->toIso8601String(), 'deletedAt' => null];
    }

    public function pullMenu(?string $since): array
    {
        $query = fn ($modelClass) => $since
            ? $modelClass::withTrashed()->where('updated_at', '>=', Carbon::parse($since))
            : $modelClass::withTrashed();

        return [
            'categories' => $query(Category::class)->get(),
            'products' => $query(Product::class)->get(),
            'modifierGroups' => $query(ModifierGroup::class)->get(),
            'modifierOptions' => $query(ModifierOption::class)->get(),
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
                    $item = OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['productRemoteId'] ?? null,
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
