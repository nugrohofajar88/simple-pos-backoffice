<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtherIncome;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtherIncomeController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $incomes = $this->sync->pullOtherIncomes($request->query('since'));

        return response()->json([
            'data' => [
                'otherIncomes' => $incomes->map(fn (OtherIncome $i) => [
                    'id' => $i->id,
                    'description' => $i->description,
                    'amount' => $i->amount,
                    'createdAt' => ($i->mobile_created_at ?? $i->created_at)->toIso8601String(),
                ]),
            ],
            'serverTime' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $incomes = $request->validate(['otherIncomes' => ['required', 'array']])['otherIncomes'];

        return response()->json(['results' => $this->sync->pushOtherIncomes($incomes)]);
    }
}
