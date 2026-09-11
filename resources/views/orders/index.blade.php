@extends('layouts.app')

@section('title', 'Riwayat Order')

@section('content')
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Riwayat Order</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Pesanan</span>
                <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">{{ $stats['orderCount'] }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-headline-sm">receipt_long</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Total Penjualan</span>
                <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">Rp{{ number_format($stats['totalSales'], 0, ',', '.') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-headline-sm">payments</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Item Terjual</span>
                <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">{{ number_format($stats['totalItems'], 0, ',', '.') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-headline-sm">local_cafe</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex flex-col">
                <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Rata-rata / Order</span>
                <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">Rp{{ number_format($stats['avgOrderValue'], 0, ',', '.') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-headline-sm">bar_chart</span>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('orders.index') }}" class="bg-surface-container-lowest rounded-xl shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Cari No. Order / Pelanggan</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-outline absolute left-3 top-1/2 -translate-y-1/2 text-title-md">search</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari..."
                           class="w-full rounded-lg border border-outline-variant pl-10 pr-3 py-2 text-body-md">
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <div class="lg:col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <div class="lg:col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Metode Bayar</label>
                <select name="payment_method" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                    <option value="">Semua Metode</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}" @selected($paymentMethod === $method)>{{ ucfirst($method) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                    <option value="">Semua Status</option>
                    <option value="completed" @selected($status === 'completed')>Selesai</option>
                    <option value="voided" @selected($status === 'voided')>Dibatalkan</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-title-md">filter_alt</span> Filter
            </button>
            @if ($from || $to || $paymentMethod || $status || $search)
                <a href="{{ route('orders.index') }}" class="inline-flex items-center font-label-lg text-label-lg text-on-surface-variant px-2 py-2">Reset</a>
            @endif
        </div>
    </form>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-body-md min-w-[880px]">
            <thead class="bg-surface-container-low text-left font-label-sm text-label-sm text-on-surface-variant">
                <tr>
                    <th class="px-4 py-2">No. Order</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2">Pelanggan</th>
                    <th class="px-4 py-2 min-w-[200px]">Item</th>
                    <th class="px-4 py-2">Bayar</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">HPP</th>
                    <th class="px-4 py-2 text-right">Margin</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse ($orders as $order)
                    @php
                        $hpp = $order->items->sum(fn ($item) => $item->cost_price * $item->qty);
                        $margin = $order->total > 0 ? round((($order->total - $hpp) / $order->total) * 100) : 0;
                        $itemPreview = $order->items->take(2)->map(fn ($item) => $item->qty.'x '.$item->product_name)->join(', ');
                        $itemMore = $order->items->count() > 2 ? ' +'.($order->items->count() - 2).' lainnya' : '';
                    @endphp
                    <tr class="hover:bg-surface-container-low cursor-pointer" onclick="window.location='{{ route('orders.show', $order) }}'">
                        <td class="px-4 py-2 font-medium text-on-surface">{{ $order->order_number }}</td>
                        <td class="px-4 py-2 text-on-surface-variant">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-on-surface">{{ $order->customer_name ?? '-' }}</td>
                        <td class="px-4 py-2 text-on-surface-variant truncate max-w-[220px]">{{ $itemPreview }}{{ $itemMore }}</td>
                        <td class="px-4 py-2 text-on-surface">{{ ucfirst($order->payment_method) }}</td>
                        <td class="px-4 py-2">
                            @if ($order->status === 'completed')
                                <span class="font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed-variant px-2 py-0.5 rounded-full font-semibold">Selesai</span>
                            @else
                                <span class="font-label-sm text-label-sm bg-error-container text-on-error-container px-2 py-0.5 rounded-full font-semibold">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-on-surface-variant">Rp{{ number_format($hpp, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">
                            <span class="font-label-sm text-label-sm bg-secondary-fixed/40 text-secondary px-1.5 py-0.5 rounded-full font-semibold">{{ $margin }}%</span>
                        </td>
                        <td class="px-4 py-2 text-right font-medium text-on-surface">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-outline">Belum ada order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
