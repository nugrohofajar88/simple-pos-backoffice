<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    private function toJson(Product $product): array
    {
        return [
            'id' => $product->id,
            'categoryId' => $product->category_id,
            'name' => $product->name,
            'basePrice' => $product->base_price,
            'costPrice' => $product->cost_price,
            'imageUrl' => $product->image_url,
            'isActive' => $product->is_active,
            'sortOrder' => $product->sort_order,
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['required', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $product = $this->menu->createProduct($data, $request->file('image'));

        return response()->json(['data' => $this->toJson($product)], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['required', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $product = $this->menu->updateProduct(
            $product,
            $data,
            $request->file('image'),
            $request->boolean('remove_image')
        );

        return response()->json(['data' => $this->toJson($product)]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->menu->deleteProduct($product);

        return response()->json(['deleted' => true]);
    }
}
