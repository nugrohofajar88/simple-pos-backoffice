<?php

use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/menu', [MenuController::class, 'index']);
    Route::post('/categories/sync', [MenuController::class, 'syncCategories']);
    Route::post('/products/sync', [MenuController::class, 'syncProducts']);
    Route::post('/modifier-groups/sync', [MenuController::class, 'syncModifierGroups']);
    Route::post('/modifier-options/sync', [MenuController::class, 'syncModifierOptions']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/settings', [SettingController::class, 'store']);
});
