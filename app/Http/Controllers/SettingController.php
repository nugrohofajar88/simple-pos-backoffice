<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'storeName' => Setting::getValue('store_name', '-'),
            'initialCapital' => (int) Setting::getValue('initial_capital', 0),
            'hasToken' => auth()->user()->tokens()->where('name', 'mobile-sync')->exists(),
        ]);
    }

    public function generateToken(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->tokens()->where('name', 'mobile-sync')->delete();

        $token = $user->createToken('mobile-sync', ['sync']);

        return back()->with('plainToken', $token->plainTextToken);
    }
}
