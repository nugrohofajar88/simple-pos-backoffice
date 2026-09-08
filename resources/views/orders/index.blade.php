@extends('layouts.app')

@section('title', 'Riwayat Order')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Riwayat Order</h1>

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
