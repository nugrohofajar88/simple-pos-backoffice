<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\ProductIngredient;
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
            'recipe_note' => $data['recipe_note'] ?? null,
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
            'recipe_note' => array_key_exists('recipe_note', $data) ? $data['recipe_note'] : $product->recipe_note,
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

    public function createIngredient(array $data): Ingredient
    {
        return Ingredient::query()->create([
            'name' => $data['name'],
            'purchase_price' => $data['purchase_price'],
            'purchase_qty' => $data['purchase_qty'],
            'unit' => $data['unit'],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    public function updateIngredient(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update([
            'name' => $data['name'] ?? $ingredient->name,
            'purchase_price' => $data['purchase_price'] ?? $ingredient->purchase_price,
            'purchase_qty' => $data['purchase_qty'] ?? $ingredient->purchase_qty,
            'unit' => $data['unit'] ?? $ingredient->unit,
            'is_active' => $data['is_active'] ?? $ingredient->is_active,
        ]);

        // Harga beli bahan ini berubah -> HPP semua produk yg makai di resepnya ikut ke-update.
        foreach ($ingredient->productIngredients()->with('product')->get() as $recipeItem) {
            $this->recalculateProductCost($recipeItem->product);
        }

        return $ingredient;
    }

    public function deleteIngredient(Ingredient $ingredient): void
    {
        $ingredient->delete();
    }

    public function addRecipeItem(Product $product, array $data): ProductIngredient
    {
        $item = ProductIngredient::query()->create([
            'product_id' => $product->id,
            'ingredient_id' => $data['ingredient_id'],
            'qty_used' => $data['qty_used'],
        ]);

        $this->recalculateProductCost($product);

        return $item;
    }

    public function updateRecipeItem(ProductIngredient $item, array $data): ProductIngredient
    {
        $item->update([
            'qty_used' => $data['qty_used'] ?? $item->qty_used,
        ]);

        $this->recalculateProductCost($item->product);

        return $item;
    }

    public function deleteRecipeItem(ProductIngredient $item): void
    {
        $product = $item->product;
        $item->delete();
        $this->recalculateProductCost($product);
    }

    /**
     * HPP produk = SUM (harga per satuan bahan x gramasi dipakai) dari semua baris
     * resepnya. Cuma jalan kalau produk PUNYA resep - produk tanpa resep tetap pakai
     * cost_price manual (mode lama, gak dipaksa pindah ke cara ini).
     */
    public function recalculateProductCost(Product $product): void
    {
        $items = $product->recipeItems()->with('ingredient')->get();

        if ($items->isEmpty()) {
            return;
        }

        $totalCost = (int) round($items->sum(fn (ProductIngredient $item) => $item->ingredient->price_per_unit * $item->qty_used));

        $product->update(['cost_price' => $totalCost]);
    }
}
