@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <a href="{{ route('menu.index') }}" class="font-label-lg text-label-lg text-on-surface-variant mb-4 inline-block">&larr; Kembali ke Menu</a>
    <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Edit Produk — {{ $product->name }}</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-3">Info Produk</h2>
            <form method="POST" action="{{ route('menu.products.update', $product) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Kategori</label>
                    <select name="category_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($category->id === $product->category_id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Nama Produk</label>
                    <input type="text" name="name" value="{{ $product->name }}" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Harga Dasar</label>
                        <input type="number" name="base_price" value="{{ $product->base_price }}" required min="0" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant mb-1">HPP</label>
                        <input type="number" name="cost_price" value="{{ $product->cost_price }}" min="0" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                    </div>
                </div>
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Resep / Cara Racik (opsional)</label>
                    <textarea name="recipe_note" rows="4" placeholder="mis. 1 shot espresso, 150ml susu steam, gula aren 15ml..."
                              class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">{{ $product->recipe_note }}</textarea>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Muncul di app kasir saat pegawai pilih menu ini - buat bantu pegawai baru racik.</p>
                </div>
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1">Gambar</label>
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-24 h-24 object-cover rounded-lg border border-outline-variant mb-2">
                        <label class="flex items-center gap-1 font-body-sm text-body-sm text-on-surface-variant mb-2">
                            <input type="checkbox" name="remove_image" value="1"> Hapus gambar saat ini
                        </label>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full text-body-sm">
                </div>
                <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-xl">
                    <span class="material-symbols-outlined text-title-md">save</span> Simpan Perubahan
                </button>
            </form>

            <form method="POST" action="{{ route('menu.products.destroy', $product) }}" class="mt-4 pt-4 border-t border-outline-variant"
                  onsubmit="return confirm('Yakin hapus produk {{ $product->name }}? Modifier terkait juga akan terhapus.')">
                @csrf @method('DELETE')
                <button type="submit" class="font-label-lg text-label-lg text-error font-medium">Hapus Produk</button>
            </form>
        </div>

        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-3">Modifier</h2>

            <div class="space-y-4">
                @forelse ($product->modifierGroups as $group)
                    <div class="border border-outline-variant rounded-lg p-3" x-data="{ editingGroup: false }">
                        <div x-show="!editingGroup" class="flex items-center justify-between mb-2">
                            <div class="font-body-sm text-body-sm font-medium text-on-surface">
                                {{ $group->name }}
                                <span class="font-body-sm text-body-sm text-outline font-normal">
                                    ({{ $group->selection_type === 'multiple' ? 'multi' : 'single' }}{{ $group->is_required ? ', wajib' : '' }})
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="editingGroup = true" class="font-label-sm text-label-sm text-primary">Edit</button>
                                <form method="POST" action="{{ route('menu.modifier-groups.destroy', $group) }}"
                                      onsubmit="return confirm('Hapus grup {{ $group->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-label-sm text-label-sm text-error">Hapus</button>
                                </form>
                            </div>
                        </div>

                        <form x-show="editingGroup" x-cloak method="POST" action="{{ route('menu.modifier-groups.update', $group) }}" class="mb-2 space-y-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $group->name }}" required class="w-full rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                            <div class="flex items-center gap-4 font-body-sm text-body-sm">
                                <label class="flex items-center gap-1">
                                    <input type="checkbox" name="selection_type_multi" @checked($group->selection_type === 'multiple')
                                           onchange="this.form.selection_type.value = this.checked ? 'multiple' : 'single'"> Boleh pilih lebih dari satu
                                </label>
                                <label class="flex items-center gap-1">
                                    <input type="checkbox" name="is_required" value="1" @checked($group->is_required)> Wajib dipilih
                                </label>
                            </div>
                            <input type="hidden" name="selection_type" value="{{ $group->selection_type }}">
                            <div class="flex items-center gap-2">
                                <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-3 py-1.5 rounded-lg">Simpan</button>
                                <button type="button" @click="editingGroup = false" class="font-label-sm text-label-sm text-on-surface-variant">Batal</button>
                            </div>
                        </form>

                        <div class="space-y-1 mb-2">
                            @foreach ($group->options as $option)
                                <div x-data="{ editingOption: false }">
                                    <div x-show="!editingOption" class="flex items-center justify-between font-body-sm text-body-sm pl-2">
                                        <span class="text-on-surface">
                                            {{ $option->name }}
                                            @if($option->price_delta > 0) (+Rp{{ number_format($option->price_delta, 0, ',', '.') }}) @endif
                                            @if($option->is_default) <span class="text-on-tertiary-container">· default</span> @endif
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="editingOption = true" class="text-primary">Edit</button>
                                            <form method="POST" action="{{ route('menu.modifier-options.destroy', $option) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-error">Hapus</button>
                                            </form>
                                        </div>
                                    </div>

                                    <form x-show="editingOption" x-cloak method="POST" action="{{ route('menu.modifier-options.update', $option) }}"
                                          class="flex items-center gap-1 pl-2 py-1">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $option->name }}" required class="flex-1 rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                        <input type="number" name="price_delta" value="{{ $option->price_delta }}" class="w-16 rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                        <label class="flex items-center gap-1 font-body-sm text-body-sm whitespace-nowrap">
                                            <input type="checkbox" name="is_default" value="1" @checked($option->is_default)> default
                                        </label>
                                        <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-2 py-1 rounded-lg">Simpan</button>
                                        <button type="button" @click="editingOption = false" class="font-label-sm text-label-sm text-on-surface-variant">Batal</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('menu.modifier-options.store', $group) }}" class="flex gap-1">
                            @csrf
                            <input type="text" name="name" placeholder="Nama opsi" required class="flex-1 rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                            <input type="number" name="price_delta" placeholder="+Rp" class="w-20 rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                            <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-2 py-1 rounded-lg">+</button>
                        </form>
                    </div>
                @empty
                    <div class="font-body-sm text-body-sm text-outline italic">Belum ada grup modifier.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('menu.modifier-groups.store', $product) }}" class="mt-4 pt-4 border-t border-outline-variant space-y-2">
                @csrf
                <div class="font-label-sm text-label-sm font-semibold text-on-surface">Tambah Grup Modifier</div>
                <input type="text" name="name" placeholder="mis. Size, Suhu, Level Gula" required class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">
                <div class="flex items-center gap-4 font-body-sm text-body-sm">
                    <label class="flex items-center gap-1"><input type="checkbox" name="selection_type_multi" onchange="this.form.selection_type.value = this.checked ? 'multiple' : 'single'"> Boleh pilih lebih dari satu</label>
                    <label class="flex items-center gap-1"><input type="checkbox" name="is_required" value="1"> Wajib dipilih</label>
                </div>
                <input type="hidden" name="selection_type" value="single">
                <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-on-primary font-label-lg text-label-lg px-4 py-2 rounded-xl">
                    <span class="material-symbols-outlined text-title-md">add</span> Tambah Grup
                </button>
            </form>
        </div>

        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface mb-1">Resep / Racikan</h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">Pilih bahan pokok &amp; gramasinya - HPP per bahan &amp; total langsung kehitung, otomatis masuk ke kolom HPP di kiri.</p>

            @if ($ingredients->isEmpty())
                <div class="font-body-sm text-body-sm text-outline italic">Belum ada bahan pokok. <a href="{{ route('ingredients.index') }}" class="text-primary underline">Tambah dulu di sini</a>.</div>
            @else
                <div class="space-y-2 mb-3">
                    @forelse ($product->recipeItems as $item)
                        <div class="flex items-center justify-between gap-2 border border-outline-variant rounded-lg px-3 py-2" x-data="{ editing: false }">
                            <div x-show="!editing" class="flex-1 min-w-0 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-body-sm text-body-sm font-medium text-on-surface truncate">{{ $item->ingredient->name }}</div>
                                    <div class="font-label-sm text-label-sm text-on-surface-variant">{{ rtrim(rtrim(number_format($item->qty_used, 2, ',', '.'), '0'), ',') }}{{ $item->ingredient->unit }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-label-md text-label-md text-primary font-semibold">Rp{{ number_format($item->cost, 0, ',', '.') }}</span>
                                    <button type="button" @click="editing = true" class="font-label-sm text-label-sm text-primary">Edit</button>
                                    <form method="POST" action="{{ route('menu.recipe-items.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="font-label-sm text-label-sm text-error">Hapus</button>
                                    </form>
                                </div>
                            </div>

                            <form x-show="editing" x-cloak method="POST" action="{{ route('menu.recipe-items.update', $item) }}" class="flex-1 flex items-center gap-2">
                                @csrf @method('PUT')
                                <span class="font-body-sm text-body-sm text-on-surface flex-1 truncate">{{ $item->ingredient->name }}</span>
                                <input type="number" step="0.01" name="qty_used" value="{{ $item->qty_used }}" required min="0.01" class="w-20 rounded-lg border border-outline-variant px-2 py-1 text-body-sm">
                                <span class="font-label-sm text-label-sm text-on-surface-variant">{{ $item->ingredient->unit }}</span>
                                <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-2 py-1 rounded-lg">Simpan</button>
                                <button type="button" @click="editing = false" class="font-label-sm text-label-sm text-on-surface-variant">Batal</button>
                            </form>
                        </div>
                    @empty
                        <div class="font-body-sm text-body-sm text-outline italic">Belum ada bahan di resep produk ini.</div>
                    @endforelse
                </div>

                @if ($product->recipeItems->isNotEmpty())
                    <div class="flex items-center justify-between px-3 py-2 bg-surface-container-low rounded-lg mb-3">
                        <span class="font-label-md text-label-md font-semibold text-on-surface">Total HPP / sajian</span>
                        <span class="font-title-md text-title-md font-bold text-primary">Rp{{ number_format($product->cost_price, 0, ',', '.') }}</span>
                    </div>
                @endif

                @php
                    $usedIngredientIds = $product->recipeItems->pluck('ingredient_id');
                    $availableIngredients = $ingredients->whereNotIn('id', $usedIngredientIds);
                @endphp

                @if ($availableIngredients->isNotEmpty())
                    <form method="POST" action="{{ route('menu.recipe-items.store', $product) }}" class="flex items-end gap-2 pt-3 border-t border-outline-variant">
                        @csrf
                        <div class="flex-1">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Bahan</label>
                            <select name="ingredient_id" required class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
                                <option value="">— pilih —</option>
                                @foreach ($availableIngredients as $ingredient)
                                    <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Gramasi</label>
                            <input type="number" step="0.01" name="qty_used" required min="0.01" class="w-full rounded-lg border border-outline-variant px-2 py-1.5 text-body-sm">
                        </div>
                        <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm px-3 py-2 rounded-lg">+</button>
                    </form>
                @else
                    <p class="font-body-sm text-body-sm text-on-surface-variant italic">Semua bahan pokok yg ada sudah dipakai di resep ini.</p>
                @endif
            @endif
        </div>
    </div>
@endsection
