<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartItemsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::customers()->get();
        $books = Book::all();

        foreach ($users as $user) {
            $cartItems = $books->random(fake()->numberBetween(0, 5));
            foreach ($cartItems as $book) {
                $user->cartItems()->create([
                    'book_id' => $book->id,
                    'quantity' => fake()->numberBetween(1, 5),
                ]);
            }
        }
    }
}
