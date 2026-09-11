@extends('layouts.app')

@section('title', 'Menu')

@section('content')
    <div x-data class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-headline-sm text-headline-sm text-primary">Kelola Menu</h1>
            <button @click="$refs.newCategoryForm.classList.toggle('hidden')"
                    class="bg-primary text-on-primary font-label-lg text-label-lg px-3 py-2 rounded-lg">+ Kategori</button>
        </div>

        <form x-ref="newCategoryForm" method="POST" action="{{ route('menu.categories.store') }}"
              class="hidden bg-surface-container-lowest border border-outline-variant rounded-xl p-4 flex gap-2 items-end">
            @csrf
            <div class="flex-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Nama Kategori</label>
                <input type="text" name="name" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
            </div>
            <button type="submit" class="bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-lg">Simpan</button>
        </form>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($categories as $category)
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden" x-data="{ editCat: false, addProduct: false }">
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
                        <a href="{{ route('menu.products.edit', $product) }}" class="flex items-center gap-3 px-4 py-2 text-body-sm hover:bg-surface-container-low">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-8 h-8 rounded object-cover">
                            @else
                                <span class="w-8 h-8 rounded bg-surface-container-low border border-outline-variant"></span>
                            @endif
                            <span class="flex-1 text-on-surface">{{ $product->name }}</span>
                            <span class="text-on-surface-variant">Rp{{ number_format($product->base_price, 0, ',', '.') }}</span>
                        </a>
                    @empty
                        <div class="px-4 py-3 font-body-sm text-body-sm text-outline italic">Belum ada produk di kategori ini.</div>
                    @endforelse
                </div>

                <div class="px-4 py-3">
                    <button @click="addProduct = !addProduct" class="font-label-sm text-label-sm text-primary font-medium">+ Produk di {{ $category->name }}</button>
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
@endsection
