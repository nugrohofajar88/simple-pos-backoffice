<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('purchase_price'); // harga beli per 1x beli (rupiah)
            $table->decimal('purchase_qty', 10, 2); // jumlah yg didapat per 1x beli (sesuai unit)
            $table->string('unit', 10); // gram, ml, pcs - harus sama dgn satuan qty_used di resep
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
