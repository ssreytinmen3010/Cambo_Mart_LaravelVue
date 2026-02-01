<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminPanel\Order;
use App\Models\AdminPanel\OrderItem;
use App\Models\AdminPanel\Product;
use App\Models\AdminPanel\Promotion;
use App\Models\User;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing orders and items
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderItem::truncate();
        Order::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = User::all();
        $products = Product::all();
        $promotions = Promotion::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create 15 sample orders
        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $promo = $promotions->isNotEmpty() && rand(1, 10) > 4 ? $promotions->random() : null;
            
            $subtotal = 0;
            $discount = 0;
            
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'promotion_id' => $promo ? $promo->id : null,
                'subtotal_amount' => 0, 
                'discount_amount' => 0,
                'total_amount' => 0,    
                'order_status' => collect([Order::ORDER_PENDING, Order::ORDER_COMPLETED, Order::ORDER_CANCELLED])->random(),
                'payment_status' => collect([Order::PAYMENT_PENDING, Order::PAYMENT_PAID])->random(),
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            // Determine products for this order
            $numItems = rand(1, 4);
            $orderProducts = $products->count() >= $numItems ? $products->random($numItems)->toBase() : $products->toBase();
            
            // If there's a promo with a specific product, make sure that product is in the order
            if ($promo && $promo->product_id) {
                $promoProduct = $products->find($promo->product_id);
                if ($promoProduct && !$orderProducts->contains('id', $promoProduct->id)) {
                    $orderProducts->push($promoProduct);
                }
            }

            foreach ($orderProducts as $product) {
                // Determine if this product is part of a promotion
                $isPromoProduct = ($promo && $promo->product_id == $product->id);
                
                if ($promo && $promo->promo_type === 'BOGO' && $isPromoProduct) {
                    $buyQty = $promo->buy_qty ?? 1;
                    $getQty = $promo->get_qty ?? 1;
                    
                    // Paid item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'qty' => $buyQty,
                        'unit_price' => $product->price,
                    ]);

                    // Free item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'qty' => $getQty,
                        'unit_price' => 0,
                    ]);
                } 
                else {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'qty' => rand(1, 3),
                        'unit_price' => $product->price,
                    ]);
                }
            }

            // Real case logic: call the model's recalculate method
            $order->recalculateTotals();
        }
    }
}
