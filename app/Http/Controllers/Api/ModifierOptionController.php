<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Support\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierOptionController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    private function toJson(ModifierOption $option): array
    {
        return [
            'id' => $option->id,
            'modifierGroupId' => $option->modifier_group_id,
            'name' => $option->name,
            'priceDelta' => $option->price_delta,
            'isDefault' => $option->is_default,
            'sortOrder' => $option->sort_order,
        ];
    }

    public function store(Request $request, ModifierGroup $modifierGroup): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_delta' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);
        $data['modifier_group_id'] = $modifierGroup->id;
        $data['is_default'] = $request->boolean('is_default');

        $option = $this->menu->createModifierOption($data);

        return response()->json(['data' => $this->toJson($option)], 201);
    }

    public function update(Request $request, ModifierOption $modifierOption): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_delta' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);
        $data['is_default'] = $request->boolean('is_default');

        $modifierOption = $this->menu->updateModifierOption($modifierOption, $data);

        return response()->json(['data' => $this->toJson($modifierOption)]);
    }

    public function destroy(ModifierOption $modifierOption): JsonResponse
    {
        $this->menu->deleteModifierOption($modifierOption);

        return response()->json(['deleted' => true]);
    }
}
