@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Pengaturan</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-lg p-4">
            <h2 class="font-semibold text-sm mb-3">Info dari Mobile</h2>
            <p class="text-xs text-gray-500 mb-3">Nama toko &amp; modal awal dikelola dari app mobile, di sini cuma tampilan (ikut ke-sync otomatis).</p>
            <div class="text-sm mb-2"><span class="text-gray-500">Nama Toko:</span> <span class="font-medium">{{ $storeName }}</span></div>
            <div class="text-sm"><span class="text-gray-500">Modal Awal:</span> <span class="font-medium">Rp{{ number_format($initialCapital, 0, ',', '.') }}</span></div>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <h2 class="font-semibold text-sm mb-3">Token Sinkronisasi Mobile</h2>

            @if (session('plainToken'))
                <div class="mb-3 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <p class="font-semibold mb-1">Token baru (cuma tampil sekali, salin sekarang):</p>
                    <code class="block break-all bg-white border rounded px-2 py-1">{{ session('plainToken') }}</code>
                </div>
            @elseif ($hasToken)
                <p class="text-xs text-gray-500 mb-3">Token sudah pernah dibuat. Kalau lupa/hilang, generate ulang (token lama otomatis nonaktif).</p>
            @else
                <p class="text-xs text-gray-500 mb-3">Belum ada token. Generate dulu, lalu tempel di app mobile (Pengaturan &gt; Sinkronisasi).</p>
            @endif

            <form method="POST" action="{{ route('settings.token') }}"
                  onsubmit="return {{ $hasToken ? "confirm('Token lama akan nonaktif, lanjutkan?')" : 'true' }}">
                @csrf
                <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">
                    {{ $hasToken ? 'Generate Ulang Token' : 'Generate Token' }}
                </button>
            </form>
        </div>
    </div>
@endsection
