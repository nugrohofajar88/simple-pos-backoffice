<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SyncService $sync)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'storeName' => ['nullable', 'string', 'max:255'],
            'initialCapital' => ['nullable', 'integer', 'min:0'],
        ]);

        $settings = [];
        if (array_key_exists('storeName', $data)) {
            $settings['store_name'] = $data['storeName'];
        }
        if (array_key_exists('initialCapital', $data)) {
            $settings['initial_capital'] = $data['initialCapital'];
        }

        $this->sync->pushSettings($settings);

        return response()->json(['status' => 'ok']);
    }
}
