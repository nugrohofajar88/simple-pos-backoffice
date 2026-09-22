@extends('layouts.app')

@section('title', 'Detail Order')

@section('content')
    <a href="{{ route('orders.index') }}" class="font-label-lg text-label-lg text-on-surface-variant mb-4 inline-block">&larr; Kembali ke Riwayat</a>

    @if (session('status'))
        <div class="mb-4 max-w-xl rounded-xl bg-tertiary-fixed px-4 py-3 font-body-sm text-body-sm text-on-tertiary-fixed-variant">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 max-w-xl rounded-xl bg-error-container px-4 py-3 font-body-sm text-body-sm text-on-error-container">{{ session('error') }}</div>
    @endif

    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4 max-w-xl">
        <div class="flex items-start justify-between gap-3">
            <h1 class="font-title-lg text-title-lg font-bold text-on-surface">{{ $order->order_number }}</h1>
            @php
                $statusBadge = match ($order->status) {
                    'pending_confirmation' => ['bg-secondary-container text-on-secondary-container', 'Menunggu Konfirmasi'],
                    'confirmed' => ['bg-primary-container text-on-primary-container', 'Dikonfirmasi'],
                    'completed' => ['bg-tertiary-fixed text-on-tertiary-fixed-variant', 'Selesai'],
                    default => ['bg-error-container text-on-error-container', 'Dibatalkan'],
                };
            @endphp
            <span class="shrink-0 font-label-sm text-label-sm {{ $statusBadge[0] }} px-2 py-0.5 rounded-full font-semibold">{{ $statusBadge[1] }}</span>
        </div>
        <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $order->mobile_created_at->translatedFormat('d M Y H:i') }}</p>
        @if ($order->customer_name)
            <p class="font-body-sm text-body-sm text-on-surface-variant">Pelanggan: {{ $order->customer_name }}</p>
        @endif
        @if ($order->customer_phone)
            <p class="font-body-sm text-body-sm text-on-surface-variant">No. WA: {{ $order->customer_phone }}</p>
        @endif
        @if ($order->source === 'app')
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                {{ $order->fulfillment_method === 'delivery' ? 'Diantar ke: '.$order->delivery_address : 'Ambil sendiri di kedai' }}
            </p>
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

        <div class="mt-4 border-t border-outline-variant pt-3 space-y-1">
            @if ($order->delivery_fee > 0)
                <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
                    <span>Subtotal</span>
                    <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-body-sm text-body-sm text-on-surface-variant">
                    <span>Ongkir</span>
                    <span>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="font-semibold text-on-surface">Total</span>
                <span class="font-title-lg text-title-lg font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        @if ($order->status === 'pending_confirmation')
            <div class="mt-4 border-t border-outline-variant pt-3">
                <h2 class="font-title-sm text-title-sm font-semibold text-on-surface mb-2">Konfirmasi Pesanan</h2>
                <form method="POST" action="{{ route('orders.confirm', $order) }}" class="space-y-3">
                    @csrf
                    @if ($order->fulfillment_method === 'delivery')
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Ongkir</label>
                            <input type="number" name="delivery_fee" min="0" step="500" placeholder="mis. 10000"
                                   class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                            <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">Kosongkan kalau gratis ongkir.</p>
                        </div>
                    @endif
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg font-semibold px-4 py-2.5 rounded-xl">
                        <span class="material-symbols-outlined text-title-md">check_circle</span> Konfirmasi & Kabari Customer (WA)
                    </button>
                </form>
                <form method="POST" action="{{ route('orders.reject', $order) }}" class="mt-2"
                      onsubmit="return confirm('Tolak pesanan {{ $order->order_number }}?')">
                    @csrf
                    <button type="submit" class="w-full font-label-lg text-label-lg text-error font-medium px-4 py-2">Tolak Pesanan</button>
                </form>
            </div>
        @elseif ($order->status === 'confirmed')
            <div class="mt-4 border-t border-outline-variant pt-3">
                <form method="POST" action="{{ route('orders.complete', $order) }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg font-semibold px-4 py-2.5 rounded-xl">
                        <span class="material-symbols-outlined text-title-md">task_alt</span> Tandai Selesai
                    </button>
                </form>
            </div>
        @endif

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
