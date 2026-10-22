<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resep/racikan per produk - baris ini yg dipakai hitung products.cost_price
     * otomatis (lihat MenuService::recalculateProductCost). Produk yg gak punya
     * baris di sini tetap pakai cost_price manual seperti sebelumnya (mode lama,
     * gak dipaksa migrasi).
     */
    public function up(): void
    {
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('qty_used', 10, 2); // satuan HARUS sama dgn ingredients.unit
            $table->timestamps();

            $table->unique(['product_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ingredients');
    }
};
