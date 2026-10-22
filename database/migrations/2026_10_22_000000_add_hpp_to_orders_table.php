<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Total HPP (harga pokok penjualan) per order, SUM(order_items.cost_price *
     * qty) - order_items.cost_price sendiri sudah snapshot dari products.cost_price
     * saat order dibuat (lihat OrderCreationService/SyncService), jadi kolom ini
     * aman dari perubahan HPP produk di masa depan. Disimpan di header biar laporan
     * gak perlu join+sum tiap order butuh angka profit.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('hpp')->default(0)->after('total');
        });

        DB::statement('
            UPDATE orders
            SET hpp = COALESCE((
                SELECT SUM(cost_price * qty)
                FROM order_items
                WHERE order_items.order_id = orders.id
            ), 0)
        ');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('hpp');
        });
    }
};
