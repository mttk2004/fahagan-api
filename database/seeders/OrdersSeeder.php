<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        User::customers()
            ->inRandomOrder()
            ->take(15)
            ->get()
            ->each(function (User $customer) {
                $order = Order::factory()->create(['user_id' => $customer->id]);

                $books = Book::inRandomOrder()->take(fake()->numberBetween(1, 5))->get();
                $totalAmount = 0.0;

                $books->each(function (Book $book) use ($order, &$totalAmount) {
                    $quantity = fake()->numberBetween(1, 5);

                    $order->orderItems()->create([
                        'book_id' => $book->id,
                        'quantity' => $quantity,
                        'price_at_time' => $book->price,
                        'discount_value' => 0,
                    ]);

                    $totalAmount += $book->price * $quantity;
                });

                $order->update(['total_amount' => $totalAmount]);
            });
    }
}
