<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            
            // Assuming you have a users table. If not, remove constrained()
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();

            // Money Fields
            $table->decimal('subtotal_amount', 12, 2); // Price before discount
            $table->decimal('discount_amount', 12, 2)->default(0); // How much removed
            $table->decimal('total_amount', 12, 2);    // Final price

            // Statuses
            $table->string('order_status');   // e.g., "PENDING", "COMPLETED", "CANCELLED", "REFUNDED"
            $table->string('payment_status');// e.g., "PENDING", "PAID", "FAILED", "REFUNDED"
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};