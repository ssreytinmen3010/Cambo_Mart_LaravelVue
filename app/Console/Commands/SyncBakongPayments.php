<?php

namespace App\Console\Commands;

use App\Models\AdminPanel\Order;
use App\Services\Bakong\BakongKhqrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncBakongPayments extends Command
{
    protected $signature = 'bakong:sync-pending-orders {--order_number=}';

    protected $description = 'Verify pending Bakong payments and update order records';

    public function handle(BakongKhqrService $bakong): int
    {
        $query = Order::query()
            ->where('payment_method', Order::PAYMENT_METHOD_ONLINE)
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->whereNotNull('bakong_md5')
            ->with(['items', 'user', 'address']);

        if ($orderNumber = $this->option('order_number')) {
            $query->where('order_number', $orderNumber);
        }

        $orders = $query->orderBy('created_at')->get();

        if ($orders->isEmpty()) {
            $this->info('No pending Bakong payments found.');
            return self::SUCCESS;
        }

        $processed = 0;
        $verified = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $processed++;

            if ($order->bakong_expires_at && now()->greaterThan($order->bakong_expires_at)) {
                $this->restoreStock($order);

                $order->forceFill([
                    'payment_status' => Order::PAYMENT_FAILED,
                    'order_status' => Order::ORDER_CANCELLED,
                    'bakong_response' => [
                        'status' => 'EXPIRED',
                        'message' => 'QR expired before payment was confirmed.',
                    ],
                ])->save();

                $failed++;
                continue;
            }

            try {
                $response = $bakong->checkTransactionByMd5($order->bakong_md5);
            } catch (Throwable $e) {
                $this->warn("{$order->order_number}: {$e->getMessage()}");
                continue;
            }

            $responseCode = data_get($response, 'responseCode');
            $statusMessage = data_get($response, 'responseMessage');
            $transaction = data_get($response, 'data');

            if ($responseCode !== 0 || ! is_array($transaction)) {
                $order->forceFill([
                    'bakong_response' => $response,
                ])->save();

                continue;
            }

            $amount = number_format((float) data_get($transaction, 'amount', 0), 2, '.', '');
            $expectedAmount = number_format((float) $order->total_amount, 2, '.', '');
            $toAccountId = data_get($transaction, 'toAccountId');
            $transactionHash = data_get($transaction, 'hash');

            if ($amount !== $expectedAmount || $toAccountId !== config('services.bakong.account_id')) {
                $this->restoreStock($order);

                $order->forceFill([
                    'payment_status' => Order::PAYMENT_FAILED,
                    'order_status' => Order::ORDER_CANCELLED,
                    'bakong_response' => $response,
                ])->save();

                $this->warn("{$order->order_number}: payment matched by md5 but amount/account did not match.");
                continue;
            }

            DB::transaction(function () use ($order, $response, $transaction, $transactionHash) {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedOrder || $lockedOrder->payment_status === Order::PAYMENT_PAID) {
                    return;
                }

                $lockedOrder->forceFill([
                    'payment_status' => Order::PAYMENT_PAID,
                    'order_status' => Order::ORDER_PENDING,
                    'bakong_transaction_hash' => $transactionHash,
                    'bakong_verified_at' => now(),
                    'bakong_response' => $response,
                ])->save();
            });

            $verified++;
        }

        $this->info("Processed {$processed} order(s). Verified: {$verified}. Expired/failed: {$failed}.");

        return self::SUCCESS;
    }

    private function restoreStock(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with('items')
                ->first();

            if (! $lockedOrder) {
                return;
            }

            foreach ($lockedOrder->items as $item) {
                $product = \App\Models\AdminPanel\Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                $product->stock = (int) $product->stock + (int) $item->qty;
                $product->save();
            }
        });
    }
}
