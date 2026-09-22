<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Order dari kasir mobile/web TETAP langsung 'completed' (tidak berubah).
        // Order tamu (self-order) baru: pending_confirmation -> confirmed -> completed.
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending_confirmation','confirmed','completed','voided') NOT NULL DEFAULT 'completed'");

        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 20)->default('admin')->after('status');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->string('fulfillment_method', 20)->default('pickup')->after('payment_method');
            $table->text('delivery_address')->nullable()->after('fulfillment_method');
            $table->unsignedInteger('delivery_fee')->default(0)->after('delivery_address');
            $table->timestamp('confirmed_at')->nullable()->after('mobile_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['source', 'customer_phone', 'fulfillment_method', 'delivery_address', 'delivery_fee', 'confirmed_at']);
        });

        DB::statement("ALTER TABLE orders MODIFY status ENUM('completed','voided') NOT NULL DEFAULT 'completed'");
    }
};
