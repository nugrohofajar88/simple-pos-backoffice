<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModifierGroupController;
use App\Http\Controllers\ModifierOptionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu/categories', [CategoryController::class, 'store'])->name('menu.categories.store');
    Route::put('/menu/categories/{category}', [CategoryController::class, 'update'])->name('menu.categories.update');
    Route::delete('/menu/categories/{category}', [CategoryController::class, 'destroy'])->name('menu.categories.destroy');

    Route::post('/menu/products', [ProductController::class, 'store'])->name('menu.products.store');
    Route::get('/menu/products/{product}/edit', [ProductController::class, 'edit'])->name('menu.products.edit');
    Route::put('/menu/products/{product}', [ProductController::class, 'update'])->name('menu.products.update');
    Route::delete('/menu/products/{product}', [ProductController::class, 'destroy'])->name('menu.products.destroy');

    Route::post('/menu/products/{product}/modifier-groups', [ModifierGroupController::class, 'store'])->name('menu.modifier-groups.store');
    Route::put('/menu/modifier-groups/{modifierGroup}', [ModifierGroupController::class, 'update'])->name('menu.modifier-groups.update');
    Route::delete('/menu/modifier-groups/{modifierGroup}', [ModifierGroupController::class, 'destroy'])->name('menu.modifier-groups.destroy');

    Route::post('/menu/modifier-groups/{modifierGroup}/options', [ModifierOptionController::class, 'store'])->name('menu.modifier-options.store');
    Route::put('/menu/modifier-options/{modifierOption}', [ModifierOptionController::class, 'update'])->name('menu.modifier-options.update');
    Route::delete('/menu/modifier-options/{modifierOption}', [ModifierOptionController::class, 'destroy'])->name('menu.modifier-options.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/token', [SettingController::class, 'generateToken'])->name('settings.token');
});
