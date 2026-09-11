@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Pengaturan</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-3">Info dari Mobile</h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">Nama toko &amp; modal awal dikelola dari app mobile, di sini cuma tampilan (ikut ke-sync otomatis).</p>
            <div class="font-body-md text-body-md mb-2"><span class="text-on-surface-variant">Nama Toko:</span> <span class="font-medium text-on-surface">{{ $storeName }}</span></div>
            <div class="font-body-md text-body-md"><span class="text-on-surface-variant">Modal Awal:</span> <span class="font-medium text-on-surface">Rp{{ number_format($initialCapital, 0, ',', '.') }}</span></div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-3">Token Sinkronisasi Mobile</h2>

            @if (session('plainToken'))
                <div class="mb-3 rounded-lg border border-secondary-fixed bg-secondary-fixed px-3 py-2 font-body-sm text-body-sm text-on-secondary-fixed-variant">
                    <p class="font-semibold mb-1">Token baru (cuma tampil sekali, salin sekarang):</p>
                    <code class="block break-all bg-surface-container-lowest border border-outline-variant rounded px-2 py-1">{{ session('plainToken') }}</code>
                </div>
            @elseif ($hasToken)
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">Token sudah pernah dibuat. Kalau lupa/hilang, generate ulang (token lama otomatis nonaktif).</p>
            @else
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">Belum ada token. Generate dulu, lalu tempel di app mobile (Pengaturan &gt; Sinkronisasi).</p>
            @endif

            <form method="POST" action="{{ route('settings.token') }}"
                  onsubmit="return {{ $hasToken ? "confirm('Token lama akan nonaktif, lanjutkan?')" : 'true' }}">
                @csrf
                <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-lg">
                    {{ $hasToken ? 'Generate Ulang Token' : 'Generate Token' }}
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-surface-container-lowest border border-error rounded-xl p-4">
        <h2 class="font-title-md text-title-md font-semibold text-error mb-2">Zona Berbahaya</h2>
        <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">
            Reset menghapus PERMANEN semua kategori, produk, modifier, order, dan belanja (dari semua HP yg sync ke sini).
            Akun login &amp; token API mobile TIDAK ikut terhapus, gak perlu pairing ulang. Tindakan ini tidak bisa dibatalkan.
        </p>

        <form method="POST" action="{{ route('settings.reset') }}"
              x-data="{ confirmText: '' }"
              onsubmit="return confirm('Yakin? Semua data akan hilang permanen dan tidak bisa dikembalikan.')">
            @csrf
            <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Ketik <span class="font-mono font-semibold">HAPUS SEMUA</span> untuk konfirmasi</label>
            <input type="text" name="confirm" x-model="confirmText" autocomplete="off"
                   class="w-full max-w-xs rounded-lg border border-outline-variant px-3 py-2 text-body-md mb-3">
            <button type="submit" :disabled="confirmText !== 'HAPUS SEMUA'"
                    :class="confirmText === 'HAPUS SEMUA' ? 'bg-error' : 'bg-surface-variant cursor-not-allowed'"
                    class="text-on-error font-label-lg text-label-lg px-4 py-2 rounded-lg">
                Reset Semua Data
            </button>
        </form>

        @error('confirm')
            <p class="font-body-sm text-body-sm text-error mt-2">Ketikan konfirmasi tidak sesuai.</p>
        @enderror
    </div>
@endsection
