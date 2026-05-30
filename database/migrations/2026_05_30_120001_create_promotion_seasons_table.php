<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_seasons', function (Blueprint $table) {
            $table->id();

            $table->string('image')->nullable();
            $table->string('code')->unique();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();

            // ACTIVE | DRAFT | PAUSE | EXPIRE
            $table->string('status')->default('DRAFT');

            $table->timestamps();

          


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_seasons');
    }
};
