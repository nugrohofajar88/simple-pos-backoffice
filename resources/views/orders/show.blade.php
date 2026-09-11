@extends('layouts.app')

@section('title', 'Detail Order')

@section('content')
    <a href="{{ route('orders.index') }}" class="font-label-lg text-label-lg text-on-surface-variant mb-4 inline-block">&larr; Kembali ke Riwayat</a>

    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 max-w-xl">
        <h1 class="font-title-lg text-title-lg font-bold text-on-surface">{{ $order->order_number }}</h1>
        <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</p>
        @if ($order->customer_name)
            <p class="font-body-sm text-body-sm text-on-surface-variant">Pelanggan: {{ $order->customer_name }}</p>
        @endif
        <p class="font-body-sm text-body-sm text-on-surface-variant">Bayar: {{ $order->payment_method }}</p>
        @if ($order->note)
            <p class="font-body-sm text-body-sm text-on-surface-variant">Catatan: {{ $order->note }}</p>
        @endif

        <div class="mt-4 border-t border-outline-variant pt-3 space-y-2">
            @foreach ($order->items as $item)
                <div class="flex justify-between font-body-md text-body-md">
                    <div>
                        <div class="font-medium text-on-surface">{{ $item->qty }}x {{ $item->product_name }}</div>
                        @if ($item->modifiers->isNotEmpty())
                            <div class="font-body-sm text-body-sm text-on-surface-variant">{{ $item->modifiers->pluck('modifier_option_name')->join(', ') }}</div>
                        @endif
                        @if ($item->note)
                            <div class="font-body-sm text-body-sm text-outline italic">Catatan: {{ $item->note }}</div>
                        @endif
                    </div>
                    <div class="font-body-md text-body-md text-on-surface">
                        Rp{{ number_format(($item->unit_price + $item->modifiers->sum('price_delta')) * $item->qty, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 border-t border-outline-variant pt-3 flex justify-between">
            <span class="font-semibold text-on-surface">Total</span>
            <span class="font-title-lg text-title-lg font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
        </div>

        <div class="mt-4 border-t border-outline-variant pt-3">
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-2">
                Order gak bisa diedit — kalau ada yang salah, hapus lalu buat order baru dari mobile.
                Menghapus di sini cuma menghapus catatan/laporan di web, order lokal di HP yang bikin
                order ini TIDAK ikut terhapus.
            </p>
            <form method="POST" action="{{ route('orders.destroy', $order) }}"
                  onsubmit="return confirm('Yakin hapus order {{ $order->order_number }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="font-label-lg text-label-lg text-error font-medium">Hapus Order</button>
            </form>
        </div>
    </div>
@endsection
