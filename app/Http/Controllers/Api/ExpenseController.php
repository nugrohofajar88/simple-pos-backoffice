<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $expenses = $this->sync->pullExpenses($request->query('since'));

        return response()->json([
            'data' => [
                'expenses' => $expenses->map(fn (Expense $e) => [
                    'id' => $e->id,
                    'description' => $e->description,
                    'amount' => $e->amount,
                    'createdAt' => ($e->mobile_created_at ?? $e->created_at)->toIso8601String(),
                ]),
            ],
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $expenses = $request->validate(['expenses' => ['required', 'array']])['expenses'];

        return response()->json(['results' => $this->sync->pushExpenses($expenses)]);
    }
}
