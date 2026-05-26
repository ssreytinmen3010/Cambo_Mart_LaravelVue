<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // For percentage promos, store the percent value (e.g. 20.00 for 20%)
            $table->decimal('discount_value', 10, 2)->default(0)->after('price');
            // Store per-unit discount amount (base_price - price)
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['discount_value', 'discount_amount']);
        });
    }
};
