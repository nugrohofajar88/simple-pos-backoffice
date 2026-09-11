@extends('layouts.app')

@section('title', 'Menu')

@section('content')
    <div x-data class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Kelola Menu</h1>
            <button @click="$refs.newCategoryForm.classList.toggle('hidden')"
                    class="bg-gray-900 text-white text-sm px-3 py-2 rounded">+ Kategori</button>
        </div>

        <form x-ref="newCategoryForm" method="POST" action="{{ route('menu.categories.store') }}"
              class="hidden bg-white border rounded-lg p-4 flex gap-2 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium mb-1">Nama Kategori</label>
                <input type="text" name="name" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Simpan</button>
        </form>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($categories as $category)
            <div class="bg-white border rounded-lg overflow-hidden" x-data="{ editCat: false, addProduct: false }">
                <div class="bg-gray-50 border-b px-4 py-3 flex items-center justify-between">
                    <span class="font-semibold" x-show="!editCat" @click="editCat = true">{{ $category->name }}</span>

                    <form x-show="editCat" method="POST" action="{{ route('menu.categories.update', $category) }}" class="flex gap-2 items-center flex-1 mr-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $category->name }}" class="rounded border border-gray-300 px-2 py-1 text-sm flex-1">
                        <button type="submit" class="text-xs text-blue-600 font-medium">Simpan</button>
                    </form>

                    <form method="POST" action="{{ route('menu.categories.destroy', $category) }}"
                          onsubmit="return confirm('Yakin hapus kategori {{ $category->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 font-medium">Hapus</button>
                    </form>
                </div>

                <div class="divide-y">
                    @forelse ($category->products as $product)
                        <a href="{{ route('menu.products.edit', $product) }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-50">
                            <span>{{ $product->name }}</span>
                            <span class="text-gray-500">Rp{{ number_format($product->base_price, 0, ',', '.') }}</span>
                        </a>
                    @empty
                        <div class="px-4 py-3 text-sm text-gray-400 italic">Belum ada produk di kategori ini.</div>
                    @endforelse
                </div>

                <div class="px-4 py-3">
                    <button @click="addProduct = !addProduct" class="text-xs text-blue-600 font-medium">+ Produk di {{ $category->name }}</button>
                    <form x-show="addProduct" method="POST" action="{{ route('menu.products.store') }}" enctype="multipart/form-data" class="mt-2 flex flex-col sm:flex-row gap-2 sm:items-end">
                        @csrf
                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                        <div class="flex-1">
                            <label class="block text-xs font-medium mb-1">Nama Produk</label>
                            <input type="text" name="name" required class="w-full rounded border border-gray-300 px-2 py-1 text-sm">
                        </div>
                        <div class="flex gap-2">
                            <div class="w-1/2 sm:w-28">
                                <label class="block text-xs font-medium mb-1">Harga</label>
                                <input type="number" name="base_price" required min="0" class="w-full rounded border border-gray-300 px-2 py-1 text-sm">
                            </div>
                            <div class="w-1/2 sm:w-28">
                                <label class="block text-xs font-medium mb-1">HPP</label>
                                <input type="number" name="cost_price" min="0" class="w-full rounded border border-gray-300 px-2 py-1 text-sm">
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium mb-1">Gambar (opsional)</label>
                            <input type="file" name="image" accept="image/*" class="w-full text-xs">
                        </div>
                        <button type="submit" class="bg-gray-900 text-white text-xs px-3 py-2 rounded">Simpan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 text-center py-8">Belum ada kategori. Tambah dulu lewat tombol di atas.</div>
        @endforelse
    </div>
@endsection
