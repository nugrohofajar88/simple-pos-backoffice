<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly MenuService $menu)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->menu->createCategory($data);

        return back()->with('status', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->menu->updateCategory($category, $data);

        return back()->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih punya produk, hapus/pindahkan produknya dulu.']);
        }

        $this->menu->deleteCategory($category);

        return back()->with('status', 'Kategori dihapus.');
    }
}
