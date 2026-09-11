<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function index(): JsonResponse
    {
        $data = $this->sync->pullMenu();

        return response()->json([
            'data' => [
                'categories' => $data['categories']->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'sortOrder' => $c->sort_order,
                    'isActive' => $c->is_active,
                ]),
                'products' => $data['products']->map(fn (Product $p) => [
                    'id' => $p->id,
                    'categoryId' => $p->category_id,
                    'name' => $p->name,
                    'basePrice' => $p->base_price,
                    'costPrice' => $p->cost_price,
                    'imageUrl' => $p->image_url,
                    'isActive' => $p->is_active,
                    'sortOrder' => $p->sort_order,
                ]),
                'modifierGroups' => $data['modifierGroups']->map(fn (ModifierGroup $g) => [
                    'id' => $g->id,
                    'productId' => $g->product_id,
                    'name' => $g->name,
                    'selectionType' => $g->selection_type,
                    'isRequired' => $g->is_required,
                    'sortOrder' => $g->sort_order,
                ]),
                'modifierOptions' => $data['modifierOptions']->map(fn (ModifierOption $o) => [
                    'id' => $o->id,
                    'modifierGroupId' => $o->modifier_group_id,
                    'name' => $o->name,
                    'priceDelta' => $o->price_delta,
                    'isDefault' => $o->is_default,
                    'sortOrder' => $o->sort_order,
                ]),
            ],
        ]);
    }
}
