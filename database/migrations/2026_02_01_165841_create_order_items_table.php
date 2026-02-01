<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            
            // Snapshot of the sale
            $table->integer('qty');
            $table->decimal('unit_price', 10, 2); // Price at moment of purchase
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};