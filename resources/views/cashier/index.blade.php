@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
    <div
        x-data="{
            categories: @json($categories),
            cart: [],
            activeCategoryId: null,
            paymentMethod: 'Cash',
            customerName: '',
            note: '',
            pickerProduct: null,
            pickerSelections: {},
            pickerQty: 1,
            pickerNote: '',

            init() {
                if (this.categories.length > 0) this.activeCategoryId = this.categories[0].id;
            },

            get activeCategory() {
                return this.categories.find(c => c.id === this.activeCategoryId) ?? null;
            },

            money(v) {
                return 'Rp' + Number(v).toLocaleString('id-ID');
            },

            openPicker(product) {
                this.pickerProduct = product;
                this.pickerQty = 1;
                this.pickerNote = '';
                const selections = {};
                for (const group of product.modifier_groups) {
                    selections[group.id] = group.options.filter(o => o.is_default).map(o => o.id);
                }
                this.pickerSelections = selections;
            },

            closePicker() {
                this.pickerProduct = null;
            },

            toggleOption(group, optionId) {
                const current = this.pickerSelections[group.id] ?? [];
                if (group.selection_type === 'single') {
                    this.pickerSelections[group.id] = [optionId];
                } else if (current.includes(optionId)) {
                    this.pickerSelections[group.id] = current.filter(id => id !== optionId);
                } else {
                    this.pickerSelections[group.id] = [...current, optionId];
                }
            },

            isSelected(group, optionId) {
                return (this.pickerSelections[group.id] ?? []).includes(optionId);
            },

            get pickerModifiersTotal() {
                if (!this.pickerProduct) return 0;
                let total = 0;
                for (const group of this.pickerProduct.modifier_groups) {
                    const selected = this.pickerSelections[group.id] ?? [];
                    for (const option of group.options) {
                        if (selected.includes(option.id)) total += option.price_delta;
                    }
                }
                return total;
            },

            get pickerUnitTotal() {
                return (this.pickerProduct?.base_price ?? 0) + this.pickerModifiersTotal;
            },

            confirmAddToCart() {
                const product = this.pickerProduct;
                if (!product) return;

                for (const group of product.modifier_groups) {
                    if (group.is_required && (this.pickerSelections[group.id] ?? []).length === 0) {
                        alert('Pilih dulu \'' + group.name + '\'.');
                        return;
                    }
                }

                const modifiers = [];
                for (const group of product.modifier_groups) {
                    const selected = this.pickerSelections[group.id] ?? [];
                    for (const option of group.options) {
                        if (selected.includes(option.id)) {
                            modifiers.push({
                                optionId: option.id,
                                groupName: group.name,
                                optionName: option.name,
                                priceDelta: option.price_delta,
                            });
                        }
                    }
                }

                this.cart.push({
                    cartId: Date.now() + '-' + Math.random().toString(36).slice(2),
                    productId: product.id,
                    productName: product.name,
                    unitPrice: product.base_price,
                    qty: this.pickerQty,
                    note: this.pickerNote,
                    modifiers,
                });
                this.closePicker();
            },

            cartItemTotal(item) {
                const modTotal = item.modifiers.reduce((sum, m) => sum + m.priceDelta, 0);
                return (item.unitPrice + modTotal) * item.qty;
            },

            get cartSubtotal() {
                return this.cart.reduce((sum, item) => sum + this.cartItemTotal(item), 0);
            },

            removeCartItem(cartId) {
                this.cart = this.cart.filter(i => i.cartId !== cartId);
            },

            updateCartQty(cartId, delta) {
                const item = this.cart.find(i => i.cartId === cartId);
                if (!item) return;
                item.qty += delta;
                if (item.qty <= 0) this.removeCartItem(cartId);
            },

            submitPayload() {
                return JSON.stringify(this.cart.map(item => ({
                    productId: item.productId,
                    qty: item.qty,
                    note: item.note || null,
                    modifierOptionIds: item.modifiers.map(m => m.optionId),
                })));
            },

            beforeSubmit(event) {
                if (this.cart.length === 0) {
                    event.preventDefault();
                    alert('Keranjang masih kosong.');
                }
            },
        }"
        class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-4 items-start"
    >
        <div>
            <h1 class="font-headline-sm text-headline-sm text-primary mb-4">Kasir</h1>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-error bg-error-container px-4 py-2 font-body-sm text-body-sm text-on-error-container">
                    {{ $errors->first() }}
                </div>
            @endif

            @if ($categories->isEmpty())
                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6 text-center font-body-md text-body-md text-on-surface-variant">
                    Belum ada kategori/produk aktif. Tambah dulu lewat menu Menu.
                </div>
            @else
                <div class="flex gap-2 overflow-x-auto pb-2 mb-4">
                    <template x-for="category in categories" :key="category.id">
                        <button
                            type="button"
                            @click="activeCategoryId = category.id"
                            :class="activeCategoryId === category.id ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface-variant'"
                            class="shrink-0 px-4 py-2 rounded-full font-label-lg text-label-lg font-medium shadow-sm"
                            x-text="category.name"
                        ></button>
                    </template>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <template x-for="product in (activeCategory?.products ?? [])" :key="product.id">
                        <button
                            type="button"
                            @click="openPicker(product)"
                            class="bg-surface-container-lowest rounded-xl shadow-sm p-3 text-left hover:shadow-md transition-shadow flex flex-col gap-2"
                        >
                            <div class="w-full aspect-square rounded-lg bg-surface-container-low overflow-hidden flex items-center justify-center">
                                <img x-show="product.image_url" :src="product.image_url" class="w-full h-full object-cover">
                                <span x-show="!product.image_url" class="material-symbols-outlined text-title-lg text-outline">local_cafe</span>
                            </div>
                            <div class="font-title-sm text-title-sm font-medium text-on-surface truncate" x-text="product.name"></div>
                            <div class="font-body-sm text-body-sm text-primary font-semibold" x-text="money(product.base_price)"></div>
                        </button>
                    </template>

                    <p x-show="(activeCategory?.products ?? []).length === 0" class="col-span-full font-body-sm text-body-sm text-outline italic py-4">
                        Belum ada produk aktif di kategori ini.
                    </p>
                </div>
            @endif
        </div>

        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-4 xl:sticky xl:top-20 flex flex-col gap-3">
            <h2 class="font-title-md text-title-md font-semibold text-on-surface">Keranjang</h2>

            <div class="flex flex-col gap-2 max-h-[40vh] overflow-y-auto">
                <template x-for="item in cart" :key="item.cartId">
                    <div class="border border-outline-variant rounded-lg p-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-body-md text-body-md font-medium text-on-surface truncate" x-text="item.productName"></div>
                                <template x-for="mod in item.modifiers" :key="mod.optionId">
                                    <div class="font-body-sm text-body-sm text-on-surface-variant" x-text="mod.groupName + ': ' + mod.optionName"></div>
                                </template>
                                <div x-show="item.note" class="font-body-sm text-body-sm text-on-surface-variant italic" x-text="'Catatan: ' + item.note"></div>
                            </div>
                            <button type="button" @click="removeCartItem(item.cartId)" class="text-error material-symbols-outlined text-title-md shrink-0">close</button>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="updateCartQty(item.cartId, -1)" class="w-6 h-6 rounded-full border border-outline-variant flex items-center justify-center font-label-md text-label-md">-</button>
                                <span class="font-label-md text-label-md font-semibold w-5 text-center" x-text="item.qty"></span>
                                <button type="button" @click="updateCartQty(item.cartId, 1)" class="w-6 h-6 rounded-full border border-outline-variant flex items-center justify-center font-label-md text-label-md">+</button>
                            </div>
                            <span class="font-label-lg text-label-lg font-semibold text-primary" x-text="money(cartItemTotal(item))"></span>
                        </div>
                    </div>
                </template>

                <p x-show="cart.length === 0" class="font-body-sm text-body-sm text-outline italic py-2">Keranjang masih kosong.</p>
            </div>

            <div class="border-t border-outline-variant pt-3 flex items-center justify-between">
                <span class="font-title-md text-title-md font-semibold text-on-surface">Total</span>
                <span class="font-headline-sm text-headline-sm font-bold text-primary" x-text="money(cartSubtotal)"></span>
            </div>

            <form method="POST" action="{{ route('cashier.store') }}" @submit="beforeSubmit" class="flex flex-col gap-2">
                @csrf
                <input type="hidden" name="items" :value="submitPayload()">
                <input type="hidden" name="payment_method" :value="paymentMethod">

                <label class="font-label-md text-label-md text-on-surface-variant">Nama Pelanggan (opsional)</label>
                <input type="text" name="customer_name" x-model="customerName" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md">

                <label class="font-label-md text-label-md text-on-surface-variant mt-1">Metode Bayar</label>
                <div class="flex gap-2">
                    <template x-for="method in ['Cash', 'QRIS', 'Debit']" :key="method">
                        <button
                            type="button"
                            @click="paymentMethod = method"
                            :class="paymentMethod === method ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'"
                            class="flex-1 px-3 py-2 rounded-lg font-label-md text-label-md font-medium"
                            x-text="method"
                        ></button>
                    </template>
                </div>

                <label class="font-label-md text-label-md text-on-surface-variant mt-1">Catatan Order (opsional)</label>
                <textarea name="note" x-model="note" rows="2" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md"></textarea>

                <button
                    type="submit"
                    :disabled="cart.length === 0"
                    :class="cart.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    class="mt-2 w-full bg-primary text-on-primary font-label-lg text-label-lg font-semibold py-3 rounded-xl"
                >
                    Selesaikan & Simpan Order
                </button>
            </form>
        </div>

        <div
            x-show="pickerProduct"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4"
            @click.self="closePicker()"
        >
            <div class="bg-surface-container-lowest w-full sm:max-w-md sm:rounded-xl rounded-t-2xl max-h-[90vh] overflow-y-auto p-4" x-cloak>
                <template x-if="pickerProduct">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-title-md text-title-md font-semibold text-on-surface" x-text="pickerProduct.name"></div>
                                <div class="font-body-sm text-body-sm text-on-surface-variant" x-text="money(pickerProduct.base_price)"></div>
                            </div>
                            <button type="button" @click="closePicker()" class="material-symbols-outlined text-title-lg text-on-surface-variant">close</button>
                        </div>

                        <div x-show="pickerProduct.recipe_note" class="bg-surface-container-low rounded-lg p-3">
                            <div class="font-label-sm text-label-sm font-semibold text-on-surface mb-1">📋 Resep / Cara Racik</div>
                            <div class="font-body-sm text-body-sm text-on-surface-variant whitespace-pre-line" x-text="pickerProduct.recipe_note"></div>
                        </div>

                        <template x-for="group in pickerProduct.modifier_groups" :key="group.id">
                            <div>
                                <div class="font-label-lg text-label-lg font-medium text-on-surface mb-1.5">
                                    <span x-text="group.name"></span>
                                    <span x-show="group.is_required" class="text-error">*</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="option in group.options" :key="option.id">
                                        <button
                                            type="button"
                                            @click="toggleOption(group, option.id)"
                                            :class="isSelected(group, option.id) ? 'bg-primary text-on-primary border-primary' : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant'"
                                            class="px-3 py-1.5 rounded-full border font-label-md text-label-md"
                                        >
                                            <span x-text="option.name"></span>
                                            <span x-show="option.price_delta > 0" x-text="' (+' + option.price_delta.toLocaleString('id-ID') + ')'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="font-label-md text-label-md text-on-surface-variant">Catatan (opsional)</label>
                            <input type="text" x-model="pickerNote" placeholder="mis. less ice" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-body-md mt-1">
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="font-label-lg text-label-lg font-medium text-on-surface">Jumlah</span>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="pickerQty = Math.max(1, pickerQty - 1)" class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center font-title-sm text-title-sm">-</button>
                                <span class="font-title-md text-title-md font-semibold w-6 text-center" x-text="pickerQty"></span>
                                <button type="button" @click="pickerQty++" class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center font-title-sm text-title-sm">+</button>
                            </div>
                        </div>

                        <button type="button" @click="confirmAddToCart()" class="w-full bg-primary text-on-primary font-label-lg text-label-lg font-semibold py-3 rounded-xl">
                            Tambah ke Keranjang — <span x-text="money(pickerUnitTotal * pickerQty)"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endsection
