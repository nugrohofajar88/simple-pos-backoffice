<?php

namespace App\Support;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuService
{
    public function createCategory(array $data): Category
    {
        return Category::query()->create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'] ?? $category->name,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        $category->delete();
    }

    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        return Product::query()->create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'base_price' => $data['base_price'],
            'cost_price' => $data['cost_price'] ?? 0,
            'image_path' => $image ? Storage::disk('public')->putFile('products', $image) : null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateProduct(
        Product $product,
        array $data,
        ?UploadedFile $image = null,
        bool $removeImage = false
    ): Product {
        $imagePath = $product->image_path;

        if ($image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = Storage::disk('public')->putFile('products', $image);
        } elseif ($removeImage && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        $product->update([
            'category_id' => $data['category_id'] ?? $product->category_id,
            'name' => $data['name'] ?? $product->name,
            'base_price' => $data['base_price'] ?? $product->base_price,
            'cost_price' => $data['cost_price'] ?? $product->cost_price,
            'image_path' => $imagePath,
            'is_active' => $data['is_active'] ?? $product->is_active,
            'sort_order' => $data['sort_order'] ?? $product->sort_order,
        ]);

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        foreach ($product->modifierGroups as $group) {
            $this->deleteModifierGroup($group);
        }
        $product->delete();
    }

    public function createModifierGroup(array $data): ModifierGroup
    {
        return ModifierGroup::query()->create([
            'product_id' => $data['product_id'],
            'name' => $data['name'],
            'selection_type' => $data['selection_type'],
            'is_required' => $data['is_required'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateModifierGroup(ModifierGroup $group, array $data): ModifierGroup
    {
        $group->update([
            'name' => $data['name'] ?? $group->name,
            'selection_type' => $data['selection_type'] ?? $group->selection_type,
            'is_required' => $data['is_required'] ?? $group->is_required,
            'sort_order' => $data['sort_order'] ?? $group->sort_order,
        ]);

        return $group;
    }

    public function deleteModifierGroup(ModifierGroup $group): void
    {
        $group->options()->delete();
        $group->delete();
    }

    public function createModifierOption(array $data): ModifierOption
    {
        return ModifierOption::query()->create([
            'modifier_group_id' => $data['modifier_group_id'],
            'name' => $data['name'],
            'price_delta' => $data['price_delta'] ?? 0,
            'is_default' => $data['is_default'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateModifierOption(ModifierOption $option, array $data): ModifierOption
    {
        $option->update([
            'name' => $data['name'] ?? $option->name,
            'price_delta' => $data['price_delta'] ?? $option->price_delta,
            'is_default' => $data['is_default'] ?? $option->is_default,
            'sort_order' => $data['sort_order'] ?? $option->sort_order,
        ]);

        return $option;
    }

    public function deleteModifierOption(ModifierOption $option): void
    {
        $option->delete();
    }
}
