<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Resep/cara racik, ditulis admin lewat web/app - ditampilkan ke kasir
            // di layar order (mobile) supaya pegawai baru bisa langsung belajar.
            $table->text('recipe_note')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('recipe_note');
        });
    }
};
