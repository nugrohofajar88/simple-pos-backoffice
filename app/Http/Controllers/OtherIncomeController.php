<?php

namespace App\Http\Controllers;

use App\Models\OtherIncome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtherIncomeController extends Controller
{
    public function index(): View
    {
        $otherIncomes = OtherIncome::query()->orderByDesc('created_at')->paginate(25);

        return view('other-incomes.index', [
            'otherIncomes' => $otherIncomes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        OtherIncome::query()->create($data);

        return back()->with('status', 'Pendapatan lain ditambahkan.');
    }

    public function destroy(OtherIncome $otherIncome): RedirectResponse
    {
        $otherIncome->delete();

        return back()->with('status', 'Pendapatan lain dihapus.');
    }
}
