<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Support\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecipeItemController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'qty_used' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($product->recipeItems()->where('ingredient_id', $data['ingredient_id'])->exists()) {
            return back()->withErrors(['ingredient_id' => 'Bahan ini sudah ada di resep produk ini.']);
        }

        $this->menu->addRecipeItem($product, $data);

        return back()->with('status', 'Bahan resep ditambahkan, HPP produk diperbarui.');
    }

    public function update(Request $request, ProductIngredient $recipeItem): RedirectResponse
    {
        $data = $request->validate([
            'qty_used' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->menu->updateRecipeItem($recipeItem, $data);

        return back()->with('status', 'Resep diperbarui, HPP produk diperbarui.');
    }

    public function destroy(ProductIngredient $recipeItem): RedirectResponse
    {
        $this->menu->deleteRecipeItem($recipeItem);

        return back()->with('status', 'Bahan dihapus dari resep, HPP produk diperbarui.');
    }
}
