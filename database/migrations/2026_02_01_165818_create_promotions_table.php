<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name'); 

             $table->unsignedBigInteger('product_id')->nullable();
            
            // Logic: "PERCENTAGE" or "BOGO"
            $table->string('promo_type'); 
            
            // Example: 20.00 (for %) or 100.00 (value)
            $table->decimal('discount_value', 10, 2); 

            // For Buy X Get Y (Nullable because Percentage type doesn't need them)
            $table->integer('buy_qty')->nullable();
            $table->integer('get_qty')->nullable();

            // Lifecycle: "ACTIVE", "PAUSED", "DRAFT","EXPIRED"
            $table->string('status'); 
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};