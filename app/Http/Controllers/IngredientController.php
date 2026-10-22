<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Support\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    public function index(): View
    {
        return view('menu.ingredients', [
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_qty' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'in:gram,ml,pcs'],
        ]);

        $this->menu->createIngredient($data);

        return back()->with('status', 'Bahan pokok ditambahkan.');
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'purchase_qty' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'in:gram,ml,pcs'],
        ]);

        $this->menu->updateIngredient($ingredient, $data);

        return back()->with('status', 'Bahan pokok diperbarui - HPP produk yg makai bahan ini ikut ke-update.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->productIngredients()->exists()) {
            return back()->withErrors(['ingredient' => 'Bahan ini masih dipakai di resep produk, hapus dulu dari resepnya.']);
        }

        $this->menu->deleteIngredient($ingredient);

        return back()->with('status', 'Bahan pokok dihapus.');
    }
}
