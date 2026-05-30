<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->decimal('fee_amount_per', 12, 2)->default(0);
            $table->decimal('qty_kg', 10, 3)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // fix | percentage | free
            $table->string('discount_type')->default('fix');
            $table->decimal('discount_value', 12, 2)->nullable();

            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();

            $table->timestamps();



          
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
