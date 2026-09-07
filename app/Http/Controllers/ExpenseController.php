<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::query()->orderByDesc('created_at')->paginate(25);

        return view('expenses.index', [
            'expenses' => $expenses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        Expense::query()->create($data);

        return back()->with('status', 'Belanja ditambahkan.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('status', 'Belanja dihapus.');
    }
}
