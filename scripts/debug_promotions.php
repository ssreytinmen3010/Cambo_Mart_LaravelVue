<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$statuses = DB::table('promotions')
    ->select('status')
    ->distinct()
    ->orderBy('status')
    ->pluck('status')
    ->all();

echo "Promotion statuses:\n";
foreach ($statuses as $status) {
    echo "- {$status}\n";
}

$sample = DB::table('promotions')
    ->select(['id', 'code', 'product_id', 'promo_type', 'discount_value', 'status', 'start_date', 'end_date'])
    ->orderByDesc('id')
    ->limit(5)
    ->get();

echo "\nLatest 5 promotions:\n";
foreach ($sample as $p) {
    echo "- id={$p->id} code={$p->code} product_id={$p->product_id} status={$p->status} start={$p->start_date} end={$p->end_date}\n";
}

