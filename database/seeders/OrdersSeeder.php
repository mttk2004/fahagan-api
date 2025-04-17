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
          ->take(5)
          ->get()
          ->each(function (User $customer) {
              $order = Order::factory()->create(['user_id' => $customer->id]);

              $books = Book::where('available_count', '>', 0)
                ->inRandomOrder()
                ->take(fake()->numberBetween(1, 3))
                ->get();

              if ($books->isEmpty()) {
                  $order->delete();

                  return;
              }

              $totalAmount = 0.0;

              $books->each(function (Book $book) use ($order, &$totalAmount) {
                  $availableCount = $book->available_count;

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

                  // Giảm số lượng sách available_count
                  $book->decrement('available_count', $quantity);

                  // Tăng số lượng sách đã bán
                  $book->increment('sold_count', $quantity);

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
