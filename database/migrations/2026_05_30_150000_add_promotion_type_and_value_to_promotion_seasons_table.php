<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_seasons', function (Blueprint $table) {
            $table->string('promotion_type', 50)->nullable()->after('end_date');
            $table->decimal('promotion_value', 12, 2)->default(0)->after('promotion_type');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_seasons', function (Blueprint $table) {
            $table->dropColumn(['promotion_type', 'promotion_value']);
        });
    }
};
