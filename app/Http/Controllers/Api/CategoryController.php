<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    private function toJson(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'sortOrder' => $category->sort_order,
            'isActive' => $category->is_active,
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = $this->menu->createCategory($data);

        return response()->json(['data' => $this->toJson($category)], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = $this->menu->updateCategory($category, $data);

        return response()->json(['data' => $this->toJson($category)]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Kategori masih punya produk, hapus/pindahkan produknya dulu.'], 422);
        }

        $this->menu->deleteCategory($category);

        return response()->json(['deleted' => true]);
    }
}
