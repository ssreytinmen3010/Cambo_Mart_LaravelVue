<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete(); // FK → roles.id

            $table->string('name', 255);
            $table->string('email')->unique();
            $table->string('phone', 50)->unique()->nullable();
            $table->string('password');

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
