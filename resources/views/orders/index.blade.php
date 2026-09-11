@extends('layouts.app')

@section('title', 'Riwayat Order')

@section('content')
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Riwayat Order</h1>

    <form method="GET" action="{{ route('orders.index') }}" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 mb-4 flex flex-col sm:flex-row gap-3 sm:items-end">
        <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-outline-variant px-3 py-2 text-body-md">
        </div>
        <div>
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-outline-variant px-3 py-2 text-body-md">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-lg">Filter</button>
            @if ($from || $to)
                <a href="{{ route('orders.index') }}" class="font-label-lg text-label-lg text-on-surface-variant px-2 py-2">Reset</a>
            @endif
        </div>
    </form>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto">
        <table class="w-full text-body-md min-w-[640px]">
            <thead class="bg-surface-container-low text-left font-label-sm text-label-sm text-on-surface-variant">
                <tr>
                    <th class="px-4 py-2">No. Order</th>
                    <th class="px-4 py-2">Pelanggan</th>
                    <th class="px-4 py-2">Bayar</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse ($orders as $order)
                    <tr class="hover:bg-surface-container-low cursor-pointer" onclick="window.location='{{ route('orders.show', $order) }}'">
                        <td class="px-4 py-2 font-medium text-on-surface">{{ $order->order_number }}</td>
                        <td class="px-4 py-2 text-on-surface">{{ $order->customer_name ?? '-' }}</td>
                        <td class="px-4 py-2 text-on-surface">{{ $order->payment_method }}</td>
                        <td class="px-4 py-2 text-on-surface-variant">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right font-medium text-on-surface">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-outline">Belum ada order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
