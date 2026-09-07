<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Urutan child -> parent (aman utk FK), akun admin & token API TIDAK ikut kehapus.
     */
    private const RESETTABLE_TABLES = [
        'order_item_modifiers',
        'order_items',
        'orders',
        'expenses',
        'modifier_options',
        'modifier_groups',
        'products',
        'categories',
    ];

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

    public function resetData(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'in:HAPUS SEMUA'],
        ]);

        DB::transaction(function () {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach (self::RESETTABLE_TABLES as $table) {
                DB::table($table)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return back()->with('status', 'Semua data (menu, order, belanja) sudah direset. Akun login & token API tetap aman.');
    }
}
