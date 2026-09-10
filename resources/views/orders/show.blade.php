@extends('layouts.app')

@section('title', 'Detail Order')

@section('content')
    <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 mb-4 inline-block">&larr; Kembali ke Riwayat</a>

    <div class="bg-white border rounded-lg p-4 max-w-xl">
        <h1 class="text-lg font-bold">{{ $order->order_number }}</h1>
        <p class="text-sm text-gray-500">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</p>
        @if ($order->customer_name)
            <p class="text-sm text-gray-500">Pelanggan: {{ $order->customer_name }}</p>
        @endif
        <p class="text-sm text-gray-500">Bayar: {{ $order->payment_method }}</p>
        @if ($order->note)
            <p class="text-sm text-gray-500">Catatan: {{ $order->note }}</p>
        @endif

        <div class="mt-4 border-t pt-3 space-y-2">
            @foreach ($order->items as $item)
                <div class="flex justify-between text-sm">
                    <div>
                        <div class="font-medium">{{ $item->qty }}x {{ $item->product_name }}</div>
                        @if ($item->modifiers->isNotEmpty())
                            <div class="text-xs text-gray-500">{{ $item->modifiers->pluck('modifier_option_name')->join(', ') }}</div>
                        @endif
                        @if ($item->note)
                            <div class="text-xs text-gray-400 italic">Catatan: {{ $item->note }}</div>
                        @endif
                    </div>
                    <div class="text-sm">
                        Rp{{ number_format(($item->unit_price + $item->modifiers->sum('price_delta')) * $item->qty, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 border-t pt-3 flex justify-between">
            <span class="font-semibold">Total</span>
            <span class="font-bold text-lg">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
        </div>

        <div class="mt-4 border-t pt-3">
            <p class="text-xs text-gray-500 mb-2">
                Order gak bisa diedit — kalau ada yang salah, hapus lalu buat order baru dari mobile.
                Menghapus di sini cuma menghapus catatan/laporan di web, order lokal di HP yang bikin
                order ini TIDAK ikut terhapus.
            </p>
            <form method="POST" action="{{ route('orders.destroy', $order) }}"
                  onsubmit="return confirm('Yakin hapus order {{ $order->order_number }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 font-medium">Hapus Order</button>
            </form>
        </div>
    </div>
@endsection
