<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ModifierGroupController;
use App\Http\Controllers\Api\ModifierOptionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/menu', [MenuController::class, 'index']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::post('/products/{product}/modifier-groups', [ModifierGroupController::class, 'store']);
    Route::put('/modifier-groups/{modifierGroup}', [ModifierGroupController::class, 'update']);
    Route::delete('/modifier-groups/{modifierGroup}', [ModifierGroupController::class, 'destroy']);

    Route::post('/modifier-groups/{modifierGroup}/options', [ModifierOptionController::class, 'store']);
    Route::put('/modifier-options/{modifierOption}', [ModifierOptionController::class, 'update']);
    Route::delete('/modifier-options/{modifierOption}', [ModifierOptionController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/settings', [SettingController::class, 'store']);

    Route::get('/reports/summary', [ReportController::class, 'summary']);
});
