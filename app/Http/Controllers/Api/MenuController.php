<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->sync->pullMenu($request->query('since'));

        return response()->json([
            'data' => [
                'categories' => $data['categories']->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'sortOrder' => $c->sort_order,
                    'isActive' => $c->is_active,
                    'updatedAt' => $c->updated_at->toIso8601String(),
                    'deletedAt' => $c->deleted_at?->toIso8601String(),
                ]),
                'products' => $data['products']->map(fn (Product $p) => [
                    'id' => $p->id,
                    'categoryId' => $p->category_id,
                    'name' => $p->name,
                    'basePrice' => $p->base_price,
                    'costPrice' => $p->cost_price,
                    'isActive' => $p->is_active,
                    'sortOrder' => $p->sort_order,
                    'updatedAt' => $p->updated_at->toIso8601String(),
                    'deletedAt' => $p->deleted_at?->toIso8601String(),
                ]),
                'modifierGroups' => $data['modifierGroups']->map(fn (ModifierGroup $g) => [
                    'id' => $g->id,
                    'productId' => $g->product_id,
                    'name' => $g->name,
                    'selectionType' => $g->selection_type,
                    'isRequired' => $g->is_required,
                    'sortOrder' => $g->sort_order,
                    'updatedAt' => $g->updated_at->toIso8601String(),
                    'deletedAt' => $g->deleted_at?->toIso8601String(),
                ]),
                'modifierOptions' => $data['modifierOptions']->map(fn (ModifierOption $o) => [
                    'id' => $o->id,
                    'modifierGroupId' => $o->modifier_group_id,
                    'name' => $o->name,
                    'priceDelta' => $o->price_delta,
                    'isDefault' => $o->is_default,
                    'sortOrder' => $o->sort_order,
                    'updatedAt' => $o->updated_at->toIso8601String(),
                    'deletedAt' => $o->deleted_at?->toIso8601String(),
                ]),
            ],
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function syncCategories(Request $request): JsonResponse
    {
        $items = $request->validate(['items' => ['required', 'array']])['items'];
        $results = [];

        foreach ($items as $item) {
            $item['sort_order'] = $item['sortOrder'] ?? 0;
            $item['is_active'] = $item['isActive'] ?? true;

            $result = $this->sync->upsertSyncable(Category::class, $item, ['name', 'sort_order', 'is_active']);
            $results[] = array_merge(['localId' => $item['localId'] ?? null], $result);
        }

        return response()->json(['results' => $results]);
    }

    public function syncProducts(Request $request): JsonResponse
    {
        $items = $request->validate(['items' => ['required', 'array']])['items'];
        $results = [];

        foreach ($items as $item) {
            $item['category_id'] = $item['categoryId'] ?? null;
            $item['base_price'] = $item['basePrice'] ?? null;
            $item['cost_price'] = $item['costPrice'] ?? 0;
            $item['is_active'] = $item['isActive'] ?? true;
            $item['sort_order'] = $item['sortOrder'] ?? 0;

            $result = $this->sync->upsertSyncable(
                Product::class,
                $item,
                ['category_id', 'name', 'base_price', 'cost_price', 'is_active', 'sort_order']
            );
            $results[] = array_merge(['localId' => $item['localId'] ?? null], $result);
        }

        return response()->json(['results' => $results]);
    }

    public function syncModifierGroups(Request $request): JsonResponse
    {
        $items = $request->validate(['items' => ['required', 'array']])['items'];
        $results = [];

        foreach ($items as $item) {
            $item['product_id'] = $item['productId'] ?? null;
            $item['selection_type'] = $item['selectionType'] ?? 'single';
            $item['is_required'] = $item['isRequired'] ?? false;
            $item['sort_order'] = $item['sortOrder'] ?? 0;

            $result = $this->sync->upsertSyncable(
                ModifierGroup::class,
                $item,
                ['product_id', 'name', 'selection_type', 'is_required', 'sort_order']
            );
            $results[] = array_merge(['localId' => $item['localId'] ?? null], $result);
        }

        return response()->json(['results' => $results]);
    }

    public function syncModifierOptions(Request $request): JsonResponse
    {
        $items = $request->validate(['items' => ['required', 'array']])['items'];
        $results = [];

        foreach ($items as $item) {
            $item['modifier_group_id'] = $item['modifierGroupId'] ?? null;
            $item['price_delta'] = $item['priceDelta'] ?? 0;
            $item['is_default'] = $item['isDefault'] ?? false;
            $item['sort_order'] = $item['sortOrder'] ?? 0;

            $result = $this->sync->upsertSyncable(
                ModifierOption::class,
                $item,
                ['modifier_group_id', 'name', 'price_delta', 'is_default', 'sort_order']
            );
            $results[] = array_merge(['localId' => $item['localId'] ?? null], $result);
        }

        return response()->json(['results' => $results]);
    }
}
