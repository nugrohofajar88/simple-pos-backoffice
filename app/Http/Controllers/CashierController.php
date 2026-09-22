<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\OrderCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function __construct(private readonly OrderCreationService $orders)
    {
    }

    public function create(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->with(['products' => fn ($query) => $query->where('is_active', true)
                // ModifierGroup/ModifierOption gak punya kolom is_active - "aktif" =
                // belum soft-deleted, sudah otomatis kefilter oleh default scope SoftDeletes.
                ->with(['modifierGroups' => fn ($q) => $q->orderBy('sort_order')->with([
                    'options' => fn ($q2) => $q2->orderBy('sort_order'),
                ])])
                ->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('cashier.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:Cash,QRIS,Debit'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'string'],
        ]);

        $items = json_decode($validated['items'], true);
        if (! is_array($items) || empty($items)) {
            throw ValidationException::withMessages(['items' => 'Keranjang masih kosong.']);
        }

        $order = $this->orders->create([
            'paymentMethod' => $validated['payment_method'],
            'customerName' => $validated['customer_name'] ?? null,
            'note' => $validated['note'] ?? null,
            'items' => $items,
        ]);

        return redirect()->route('cashier.index')
            ->with('status', "Order {$order->order_number} tersimpan - Total Rp".number_format($order->total, 0, ',', '.'));
    }
}
