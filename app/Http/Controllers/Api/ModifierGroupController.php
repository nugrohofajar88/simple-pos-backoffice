<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Support\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierGroupController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    private function toJson(ModifierGroup $group): array
    {
        return [
            'id' => $group->id,
            'productId' => $group->product_id,
            'name' => $group->name,
            'selectionType' => $group->selection_type,
            'isRequired' => $group->is_required,
            'sortOrder' => $group->sort_order,
        ];
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', 'in:single,multiple'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['product_id'] = $product->id;
        $data['is_required'] = $request->boolean('is_required');

        $group = $this->menu->createModifierGroup($data);

        return response()->json(['data' => $this->toJson($group)], 201);
    }

    public function update(Request $request, ModifierGroup $modifierGroup): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', 'in:single,multiple'],
            'is_required' => ['nullable', 'boolean'],
        ]);
        $data['is_required'] = $request->boolean('is_required');

        $modifierGroup = $this->menu->updateModifierGroup($modifierGroup, $data);

        return response()->json(['data' => $this->toJson($modifierGroup)]);
    }

    public function destroy(ModifierGroup $modifierGroup): JsonResponse
    {
        $this->menu->deleteModifierGroup($modifierGroup);

        return response()->json(['deleted' => true]);
    }
}
