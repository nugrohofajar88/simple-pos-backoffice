<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->with(['products' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('menu.index', [
            'categories' => $categories,
        ]);
    }
}
