<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookInstance;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        User::customers()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(function (User $customer) {
                $order = Order::factory()->create(['user_id' => $customer->id]);

                $books = Book::has('availableInstances')
                    ->inRandomOrder()
                    ->take(fake()->numberBetween(1, 3))
                    ->get();

                if ($books->isEmpty()) {
                    $order->delete();
                    return;
                }

                $totalAmount = 0.0;

                $books->each(function (Book $book) use ($order, &$totalAmount) {
                    $availableCount = $book->bookInstances()->where('status', 'available')->count();

                    if ($availableCount <= 0) {
                        return;
                    }

                    $quantity = min(fake()->numberBetween(1, 3), $availableCount);

                    $orderItem = $order->orderItems()->create([
                        'book_id' => $book->id,
                        'quantity' => $quantity,
                        'price_at_time' => $book->price,
                        'discount_value' => 0,
                    ]);

                    $bookInstances = $book->bookInstances()
                        ->where('status', 'available')
                        ->take($quantity)
                        ->get();

                    foreach ($bookInstances as $instance) {
                        $instance->update([
                            'status' => 'sold',
                            'order_item_id' => $orderItem->id,
                            'sold_at' => now()
                        ]);
                    }

                    $totalAmount += $book->price * $quantity;
                });

                if ($totalAmount > 0) {
                    $order->update(['total_amount' => $totalAmount]);
                } else {
                    $order->delete();
                }
            });
    }
}
