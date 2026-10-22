@extends('layouts.app')

@section('title', 'Bahan Pokok')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <a href="{{ route('menu.index') }}" class="font-label-lg text-label-lg text-on-surface-variant mb-1 inline-block">&larr; Kembali ke Menu</a>
            <h1 class="font-headline-sm text-headline-sm text-primary">Bahan Pokok</h1>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Harga beli &amp; gramasi bahan baku. Dipakai buat hitung HPP otomatis di resep tiap produk.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-tertiary bg-tertiary-fixed px-4 py-2 font-body-sm text-body-sm text-on-tertiary-fixed-variant">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="divide-y divide-outline-variant">
            @forelse ($ingredients as $ingredient)
                <div class="px-4 py-3" x-data="{ editing: false }">
                    <div x-show="!editing" class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-title-md text-title-md font-medium text-on-surface truncate">{{ $ingredient->name }}</div>
                            <div class="font-body-sm text-body-sm text-on-surface-variant">
                                Beli Rp{{ number_format($ingredient->purchase_price, 0, ',', '.') }} / {{ rtrim(rtrim(number_format($ingredient->purchase_qty, 2, ',', '.'), '0'), ',') }}{{ $ingredient->unit }}
                                &middot; <span class="font-medium text-primary">Rp{{ number_format($ingredient->price_per_unit, 2, ',', '.') }}/{{ $ingredient->unit }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <button type="button" @click="editing = true" class="font-label-sm text-label-sm text-primary font-medium">Edit</button>
                            <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" onsubmit="return confirm('Hapus bahan {{ $ingredient->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="font-label-sm text-label-sm text-error font-medium">Hapus</button>
                            </form>
                        </div>
                    </div>

                    <form x-show="editing" x-cloak method="POST" action="{{ route('ingredients.update', $ingredient) }}" class="flex flex-col sm:flex-row gap-2 sm:items-end">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Nama Bahan</label>
                            <input type="text" name="name" value="{{ $ingredient->name }}" required class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                        </div>
                        <div class="w-full sm:w-32">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Harga Beli</label>
                            <input type="number" name="purchase_price" value="{{ $ingredient->purchase_price }}" required min="0" class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                        </div>
                        <div class="w-full sm:w-28">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Jumlah</label>
                            <input type="number" step="0.01" name="purchase_qty" value="{{ $ingredient->purchase_qty }}" required min="0.01" class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                        </div>
                        <div class="w-full sm:w-24">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Satuan</label>
                            <select name="unit" class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                @foreach (['gram', 'ml', 'pcs'] as $unit)
                                    <option value="{{ $unit }}" @selected($ingredient->unit === $unit)>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-3 py-1.5 rounded-lg">Simpan</button>
                            <button type="button" @click="editing = false" class="font-label-sm text-label-sm text-on-surface-variant px-2">Batal</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="px-4 py-6 font-body-md text-body-md text-on-surface-variant text-center">Belum ada bahan pokok. Tambah lewat form di bawah.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
        <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-3">Tambah Bahan Pokok</h2>
        <form method="POST" action="{{ route('ingredients.store') }}" class="flex flex-col sm:flex-row gap-2 sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Nama Bahan</label>
                <input type="text" name="name" placeholder="mis. Gula Aren" required class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
            </div>
            <div class="w-full sm:w-32">
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Harga Beli (Rp)</label>
                <input type="number" name="purchase_price" placeholder="25000" required min="0" class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
            </div>
            <div class="w-full sm:w-28">
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Jumlah</label>
                <input type="number" step="0.01" name="purchase_qty" placeholder="1000" required min="0.01" class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
            </div>
            <div class="w-full sm:w-24">
                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Satuan</label>
                <select name="unit" class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
                    <option value="gram">gram</option>
                    <option value="ml">ml</option>
                    <option value="pcs">pcs</option>
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-title-md">add</span> Tambah
            </button>
        </form>
        <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">Contoh: Gula Aren dibeli Rp25.000 per 1000 gram &rarr; otomatis kehitung Rp25/gram.</p>
    </div>
@endsection
