<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Support\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModifierOptionController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    public function store(Request $request, ModifierGroup $modifierGroup): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_delta' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);
        $data['modifier_group_id'] = $modifierGroup->id;
        $data['is_default'] = $request->boolean('is_default');

        $this->menu->createModifierOption($data);

        return back()->with('status', 'Opsi modifier ditambahkan.');
    }

    public function update(Request $request, ModifierOption $modifierOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_delta' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);
        $data['is_default'] = $request->boolean('is_default');

        $this->menu->updateModifierOption($modifierOption, $data);

        return back()->with('status', 'Opsi modifier diperbarui.');
    }

    public function destroy(ModifierOption $modifierOption): RedirectResponse
    {
        $this->menu->deleteModifierOption($modifierOption);

        return back()->with('status', 'Opsi modifier dihapus.');
    }
}
