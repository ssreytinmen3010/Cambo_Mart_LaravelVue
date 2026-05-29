<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'bakong_qr')) {
                $table->text('bakong_qr')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('orders', 'bakong_md5')) {
                $table->string('bakong_md5', 32)->nullable()->after('bakong_qr')->index();
            }

            if (! Schema::hasColumn('orders', 'bakong_expires_at')) {
                $table->timestamp('bakong_expires_at')->nullable()->after('bakong_md5');
            }

            if (! Schema::hasColumn('orders', 'bakong_transaction_hash')) {
                $table->string('bakong_transaction_hash', 255)->nullable()->after('bakong_expires_at');
            }

            if (! Schema::hasColumn('orders', 'bakong_verified_at')) {
                $table->timestamp('bakong_verified_at')->nullable()->after('bakong_transaction_hash');
            }

            if (! Schema::hasColumn('orders', 'bakong_response')) {
                $table->json('bakong_response')->nullable()->after('bakong_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'bakong_qr',
                'bakong_md5',
                'bakong_expires_at',
                'bakong_transaction_hash',
                'bakong_verified_at',
                'bakong_response',
            ];

            $existingColumns = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('orders', $column)));

            if (! empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
