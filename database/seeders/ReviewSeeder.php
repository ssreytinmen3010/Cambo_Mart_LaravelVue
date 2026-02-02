<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminPanel\Review;
use App\Models\AdminPanel\Product;
use App\Models\User;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first product and user (make sure they exist)
        $product = Product::first();
        $user = User::first();

        if (!$product || !$user) {
            $this->command->warn('Please ensure you have at least one product and one user in the database before seeding reviews.');
            return;
        }

        // Generate unique review numbers
        $lastReview = Review::orderBy('id', 'desc')->first();
        $startNumber = $lastReview ? ($lastReview->id + 1) : 1;

        $reviews = [
            [
                'review_number' => 'REV-' . str_pad($startNumber, 6, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating_score' => 5,
                'review_text' => 'Excellent product! The quality exceeded my expectations. Fast delivery and great customer service. Highly recommended!',
                'review_status' => Review::STATUS_APPROVED,
                'create_date' => Carbon::now()->subDays(5),
            ],
            [
                'review_number' => 'REV-' . str_pad($startNumber + 1, 6, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating_score' => 4,
                'review_text' => 'Good product overall. The packaging was nice and the item arrived on time. Would buy again.',
                'review_status' => Review::STATUS_PENDING,
                'create_date' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($reviews as $reviewData) {
            Review::create($reviewData);
        }

        $this->command->info('2 reviews have been seeded successfully!');
    }
}
