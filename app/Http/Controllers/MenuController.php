<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->with(['products' => fn ($query) => $query->with('modifierGroups')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('menu.index', [
            'categories' => $categories,
            'stats' => $this->stats(),
        ]);
    }

    private function stats(): array
    {
        $activeProducts = Product::query()->where('is_active', true)->get(['name', 'base_price', 'cost_price']);

        $withCost = $activeProducts->filter(fn ($p) => $p->cost_price > 0 && $p->base_price > 0);
        $margins = $withCost->map(fn ($p) => (($p->base_price - $p->cost_price) / $p->base_price) * 100);

        $topMarginProduct = $withCost->isNotEmpty()
            ? $withCost->sortByDesc(fn ($p) => ($p->base_price - $p->cost_price) / $p->base_price)->first()
            : null;

        return [
            'totalActive' => $activeProducts->count(),
            'avgMarginPercent' => $margins->isNotEmpty() ? (int) round($margins->avg()) : 0,
            'topMarginProduct' => $topMarginProduct?->name,
            'topMarginPercent' => $topMarginProduct
                ? (int) round((($topMarginProduct->base_price - $topMarginProduct->cost_price) / $topMarginProduct->base_price) * 100)
                : 0,
            'missingCostCount' => $activeProducts->where('cost_price', 0)->count(),
        ];
    }
}
