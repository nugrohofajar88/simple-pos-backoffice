<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $orders = $request->validate(['orders' => ['required', 'array']])['orders'];

        return response()->json(['results' => $this->sync->pushOrders($orders)]);
    }
}
