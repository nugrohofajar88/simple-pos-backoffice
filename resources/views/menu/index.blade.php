@extends('layouts.app')

@section('title', 'Menu')

@section('content')
    <div x-data="{ search: '' }">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-headline-sm text-headline-sm text-primary">Kelola Menu</h1>
            <div x-data class="relative">
                <button @click="$refs.newCategoryForm.classList.toggle('hidden')"
                        class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-3 py-2 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-title-md">add</span> Kategori
                </button>
                <form x-ref="newCategoryForm" method="POST" action="{{ route('menu.categories.store') }}"
                      class="hidden absolute right-0 mt-2 z-10 w-72 bg-surface-container-lowest rounded-xl shadow-md p-4 flex flex-col gap-2 items-end">
                    @csrf
                    <div class="w-full">
                        <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Nama Kategori</label>
                        <input type="text" name="name" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                    </div>
                    <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-lg">Simpan</button>
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Total Menu Aktif</span>
                    <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">{{ $stats['totalActive'] }}</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-surface-container-low flex items-center justify-center text-primary-container">
                    <span class="material-symbols-outlined text-headline-sm">local_cafe</span>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Rata-rata Margin</span>
                    <span class="font-headline-md text-headline-md text-primary font-bold leading-none mt-1">{{ $stats['avgMarginPercent'] }}%</span>
                </div>
                <div class="w-11 h-11 rounded-xl bg-secondary-fixed/40 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-headline-sm">query_stats</span>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex flex-col min-w-0">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">Margin Tertinggi</span>
                    @if ($stats['topMarginProduct'])
                        <div class="flex items-baseline gap-1 mt-1 min-w-0">
                            <span class="font-title-md text-title-md text-primary font-bold truncate">{{ $stats['topMarginProduct'] }}</span>
                            <span class="font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed-variant px-1.5 py-0.5 rounded-full font-bold shrink-0">{{ $stats['topMarginPercent'] }}%</span>
                        </div>
                    @else
                        <span class="font-body-sm text-body-sm text-on-surface-variant mt-1">Belum ada data HPP</span>
                    @endif
                </div>
                <div class="w-11 h-11 rounded-xl bg-surface-container-high flex items-center justify-center text-secondary-container">
                    <span class="material-symbols-outlined text-headline-sm">award_star</span>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider font-semibold">HPP Belum Diisi</span>
                    <span class="font-headline-md text-headline-md {{ $stats['missingCostCount'] > 0 ? 'text-error' : 'text-primary' }} font-bold leading-none mt-1">{{ $stats['missingCostCount'] }}</span>
                </div>
                <div class="w-11 h-11 rounded-xl {{ $stats['missingCostCount'] > 0 ? 'bg-error-container/40 text-error' : 'bg-surface-container-low text-primary-container' }} flex items-center justify-center">
                    <span class="material-symbols-outlined text-headline-sm">warning</span>
                </div>
            </div>
        </div>

        <div class="relative mb-4">
            <span class="material-symbols-outlined text-outline absolute left-3 top-1/2 -translate-y-1/2 text-title-md">search</span>
            <input type="text" x-model="search" placeholder="Cari nama produk..."
                   class="w-full sm:max-w-sm bg-surface-container-lowest shadow-sm pl-10 pr-4 py-2.5 rounded-xl text-body-md outline-none focus:ring-2 focus:ring-primary-container/40">
        </div>

        <div class="space-y-4">
            @forelse ($categories as $category)
                <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden" x-data="{ editCat: false, addProduct: false }">
                    <div class="bg-surface-container-low border-b border-outline-variant px-4 py-3 flex items-center justify-between">
                        <span class="font-semibold text-on-surface" x-show="!editCat" @click="editCat = true">{{ $category->name }}</span>

                        <form x-show="editCat" method="POST" action="{{ route('menu.categories.update', $category) }}" class="flex gap-2 items-center flex-1 mr-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" class="rounded-lg border border-outline-variant px-2 py-1 text-body-sm flex-1">
                            <button type="submit" class="font-label-sm text-label-sm text-primary font-medium">Simpan</button>
                        </form>

                        <form method="POST" action="{{ route('menu.categories.destroy', $category) }}"
                              onsubmit="return confirm('Yakin hapus kategori {{ $category->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="font-label-sm text-label-sm text-error font-medium">Hapus</button>
                        </form>
                    </div>

                    <div class="divide-y divide-outline-variant">
                        @forelse ($category->products as $product)
                            @php
                                $hasCost = $product->cost_price > 0 && $product->base_price > 0;
                                $margin = $hasCost ? round((($product->base_price - $product->cost_price) / $product->base_price) * 100) : null;
                            @endphp
                            <a href="{{ route('menu.products.edit', $product) }}"
                               data-name="{{ Str::lower($product->name) }}"
                               x-show="search === '' || $el.dataset.name.includes(search.toLowerCase())"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-surface-container-low">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-lg bg-surface-container-low border border-outline-variant shrink-0"></span>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="font-title-md text-title-md font-medium text-on-surface truncate">{{ $product->name }}</div>
                                    <div class="font-body-sm text-body-sm text-on-surface-variant truncate">
                                        HPP Rp{{ number_format($product->cost_price, 0, ',', '.') }}
                                        @if ($product->modifierGroups->isNotEmpty())
                                            · {{ $product->modifierGroups->count() }} grup modifier
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-title-md text-title-md font-semibold text-primary">Rp{{ number_format($product->base_price, 0, ',', '.') }}</div>
                                    @if ($margin !== null)
                                        <span class="font-label-sm text-label-sm bg-tertiary-fixed text-on-tertiary-fixed-variant px-1.5 py-0.5 rounded-full font-bold">{{ $margin }}%</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-3 font-body-sm text-body-sm text-outline italic">Belum ada produk di kategori ini.</div>
                        @endforelse
                    </div>

                    <div class="px-4 py-3">
                        <button @click="addProduct = !addProduct" class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary font-medium">
                            <span class="material-symbols-outlined text-title-md">add</span> Produk di {{ $category->name }}
                        </button>
                        <form x-show="addProduct" method="POST" action="{{ route('menu.products.store') }}" enctype="multipart/form-data" class="mt-2 flex flex-col sm:flex-row gap-2 sm:items-end">
                            @csrf
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                            <div class="flex-1">
                                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Nama Produk</label>
                                <input type="text" name="name" required class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                            </div>
                            <div class="flex gap-2">
                                <div class="w-1/2 sm:w-28">
                                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Harga</label>
                                    <input type="number" name="base_price" required min="0" class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                </div>
                                <div class="w-1/2 sm:w-28">
                                    <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">HPP</label>
                                    <input type="number" name="cost_price" min="0" class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Gambar (opsional)</label>
                                <input type="file" name="image" accept="image/*" class="w-full text-body-sm">
                            </div>
                            <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-3 py-2 rounded-lg">Simpan</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="font-body-md text-body-md text-on-surface-variant text-center py-8">Belum ada kategori. Tambah dulu lewat tombol di atas.</div>
            @endforelse
        </div>
    </div>
@endsection
