<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Models\Product;
use App\Support\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModifierGroupController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', 'in:single,multiple'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['product_id'] = $product->id;
        $data['is_required'] = $request->boolean('is_required');

        $this->menu->createModifierGroup($data);

        return back()->with('status', 'Grup modifier ditambahkan.');
    }

    public function update(Request $request, ModifierGroup $modifierGroup): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', 'in:single,multiple'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['is_required'] = $request->boolean('is_required');

        $this->menu->updateModifierGroup($modifierGroup, $data);

        return back()->with('status', 'Grup modifier diperbarui.');
    }

    public function destroy(ModifierGroup $modifierGroup): RedirectResponse
    {
        $this->menu->deleteModifierGroup($modifierGroup);

        return back()->with('status', 'Grup modifier dihapus.');
    }
}
