<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address', function (Blueprint $table) {
            $table->decimal('qty_kilo', 12, 3)->default(1)->after('floor');
        });
    }

    public function down(): void
    {
        Schema::table('address', function (Blueprint $table) {
            $table->dropColumn('qty_kilo');
        });
    }
};
