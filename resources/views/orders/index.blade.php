@extends('layouts.app')

@section('title', 'Riwayat Order')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Riwayat Order</h1>

    <form method="GET" action="{{ route('orders.index') }}" class="bg-white border rounded-lg p-4 mb-4 flex flex-col sm:flex-row gap-3 sm:items-end">
        <div>
            <label class="block text-xs font-medium mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Filter</button>
            @if ($from || $to)
                <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 px-2 py-2">Reset</a>
            @endif
        </div>
    </form>

    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-left text-xs text-gray-500">
                <tr>
                    <th class="px-4 py-2">No. Order</th>
                    <th class="px-4 py-2">Pelanggan</th>
                    <th class="px-4 py-2">Bayar</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('orders.show', $order) }}'">
                        <td class="px-4 py-2 font-medium">{{ $order->order_number }}</td>
                        <td class="px-4 py-2">{{ $order->customer_name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $order->payment_method }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right font-medium">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
