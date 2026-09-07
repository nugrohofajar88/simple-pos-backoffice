<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $expenses = $request->validate(['expenses' => ['required', 'array']])['expenses'];

        return response()->json(['results' => $this->sync->pushExpenses($expenses)]);
    }
}
