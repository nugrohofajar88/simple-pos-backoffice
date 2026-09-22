<?php

namespace App\Support;

use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bikin 1 order LANGSUNG dari web (kasir di komputer/laptop) - beda jalur dari
 * SyncService::pushOrders() yang menerima order yang SUDAH jadi dari mobile (push
 * 1 arah, order_number sudah digenerate di HP). Di sini order_number, subtotal,
 * dan snapshot harga/modifier dihitung dari data server (sumber kebenaran).
 */
class OrderCreationService
{
    /**
     * @param  array{paymentMethod:string,customerName?:?string,note?:?string,items:array<int,array{productId:int,qty:int,note?:?string,modifierOptionIds?:array<int,int>}>}  $data
     */
    public function create(array $data): Order
    {
        if (empty($data['items'])) {
            throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
        }

        return DB::transaction(function () use ($data) {
            $now = now();
            $subtotal = 0;
            $prepared = [];

            foreach ($data['items'] as $itemInput) {
                $qty = (int) ($itemInput['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $product = Product::query()->findOrFail($itemInput['productId']);
                $product->loadMissing('modifierGroups.options');

                // whereIn dgn array kosong otomatis kembalikan collection kosong - aman.
                $optionIds = $itemInput['modifierOptionIds'] ?? [];
                $validGroupIds = $product->modifierGroups->pluck('id');
                $selectedOptions = ModifierOption::query()->whereIn('id', $optionIds)->with('modifierGroup')->get()
                    // Jaga-jaga: buang opsi yg gak milik produk ini (mis. payload dari state UI yg nyasar).
                    ->filter(fn (ModifierOption $o) => $validGroupIds->contains($o->modifier_group_id))
                    ->values();

                foreach ($product->modifierGroups as $group) {
                    if (! $group->is_required) {
                        continue;
                    }
                    $hasSelection = $selectedOptions->contains(fn (ModifierOption $o) => $o->modifier_group_id === $group->id);
                    if (! $hasSelection) {
                        throw ValidationException::withMessages([
                            'items' => "\"{$group->name}\" wajib dipilih untuk {$product->name}.",
                        ]);
                    }
                }

                $modifiersTotal = (int) $selectedOptions->sum('price_delta');
                $unitPrice = (int) $product->base_price;
                $lineTotal = ($unitPrice + $modifiersTotal) * $qty;
                $subtotal += $lineTotal;

                $prepared[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'note' => $itemInput['note'] ?? null,
                    'unitPrice' => $unitPrice,
                    'options' => $selectedOptions,
                ];
            }

            if (empty($prepared)) {
                throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
            }

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'status' => 'completed',
                'customer_name' => $data['customerName'] ?? null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'payment_method' => $data['paymentMethod'],
                'note' => $data['note'] ?? null,
                'mobile_created_at' => $now,
            ]);

            foreach ($prepared as $entry) {
                $item = $order->items()->create([
                    'product_id' => $entry['product']->id,
                    'product_name' => $entry['product']->name,
                    'unit_price' => $entry['unitPrice'],
                    'cost_price' => (int) $entry['product']->cost_price,
                    'qty' => $entry['qty'],
                    'note' => $entry['note'],
                    'printed' => false,
                    'mobile_created_at' => $now,
                ]);

                foreach ($entry['options'] as $option) {
                    $item->modifiers()->create([
                        'modifier_group_name' => $option->modifierGroup->name,
                        'modifier_option_name' => $option->name,
                        'price_delta' => $option->price_delta,
                    ]);
                }
            }

            return $order->load('items.modifiers');
        });
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-WEB-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $count = Order::query()->where('order_number', 'like', $prefix.'%')->count();
            $candidate = $prefix.str_pad((string) ($count + 1 + $attempt), 3, '0', STR_PAD_LEFT);
            if (! Order::query()->where('order_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Fallback ekstrem (harusnya gak pernah kena): pastikan tetap unique.
        return $prefix.substr((string) microtime(true), -6);
    }
}
