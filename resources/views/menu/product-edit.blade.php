@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <a href="{{ route('menu.index') }}" class="text-sm text-gray-500 mb-4 inline-block">&larr; Kembali ke Menu</a>
    <h1 class="text-xl font-semibold mb-4">Edit Produk — {{ $product->name }}</h1>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border rounded-lg p-4">
            <h2 class="font-semibold text-sm mb-3">Info Produk</h2>
            <form method="POST" action="{{ route('menu.products.update', $product) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium mb-1">Kategori</label>
                    <select name="category_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($category->id === $product->category_id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Nama Produk</label>
                    <input type="text" name="name" value="{{ $product->name }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">Harga Dasar</label>
                        <input type="number" name="base_price" value="{{ $product->base_price }}" required min="0" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">HPP</label>
                        <input type="number" name="cost_price" value="{{ $product->cost_price }}" min="0" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Simpan Perubahan</button>
            </form>

            <form method="POST" action="{{ route('menu.products.destroy', $product) }}" class="mt-4 pt-4 border-t"
                  onsubmit="return confirm('Yakin hapus produk {{ $product->name }}? Modifier terkait juga akan terhapus.')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 font-medium">Hapus Produk</button>
            </form>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <h2 class="font-semibold text-sm mb-3">Modifier</h2>

            <div class="space-y-4">
                @forelse ($product->modifierGroups as $group)
                    <div class="border rounded p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-medium">
                                {{ $group->name }}
                                <span class="text-xs text-gray-400 font-normal">
                                    ({{ $group->selection_type === 'multiple' ? 'multi' : 'single' }}{{ $group->is_required ? ', wajib' : '' }})
                                </span>
                            </div>
                            <form method="POST" action="{{ route('menu.modifier-groups.destroy', $group) }}"
                                  onsubmit="return confirm('Hapus grup {{ $group->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-600">Hapus</button>
                            </form>
                        </div>

                        <div class="space-y-1 mb-2">
                            @foreach ($group->options as $option)
                                <div class="flex items-center justify-between text-xs pl-2">
                                    <span>
                                        {{ $option->name }}
                                        @if($option->price_delta > 0) (+Rp{{ number_format($option->price_delta, 0, ',', '.') }}) @endif
                                        @if($option->is_default) <span class="text-green-600">· default</span> @endif
                                    </span>
                                    <form method="POST" action="{{ route('menu.modifier-options.destroy', $option) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('menu.modifier-options.store', $group) }}" class="flex gap-1">
                            @csrf
                            <input type="text" name="name" placeholder="Nama opsi" required class="flex-1 rounded border border-gray-300 px-2 py-1 text-xs">
                            <input type="number" name="price_delta" placeholder="+Rp" class="w-20 rounded border border-gray-300 px-2 py-1 text-xs">
                            <button type="submit" class="bg-gray-900 text-white text-xs px-2 py-1 rounded">+</button>
                        </form>
                    </div>
                @empty
                    <div class="text-xs text-gray-400 italic">Belum ada grup modifier.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('menu.modifier-groups.store', $product) }}" class="mt-4 pt-4 border-t space-y-2">
                @csrf
                <div class="text-xs font-semibold">Tambah Grup Modifier</div>
                <input type="text" name="name" placeholder="mis. Size, Suhu, Level Gula" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                <div class="flex items-center gap-4 text-xs">
                    <label class="flex items-center gap-1"><input type="checkbox" name="selection_type_multi" onchange="this.form.selection_type.value = this.checked ? 'multiple' : 'single'"> Boleh pilih lebih dari satu</label>
                    <label class="flex items-center gap-1"><input type="checkbox" name="is_required"> Wajib dipilih</label>
                </div>
                <input type="hidden" name="selection_type" value="single">
                <button type="submit" class="bg-gray-900 text-white text-sm px-4 py-2 rounded">Tambah Grup</button>
            </form>
        </div>
    </div>
@endsection
