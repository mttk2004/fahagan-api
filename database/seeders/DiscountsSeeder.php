<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Discount;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

class DiscountsSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo chương trình giảm giá
        $discount = Discount::create([
            'name' => 'Giảm giá Tết 2025',
            'discount_type' => 'percent',
            'discount_value' => 15,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
        ]);

        // Gán giảm giá cho một số sách
        $books = Book::take(3)->get();
        foreach ($books as $book) {
            $discount->targets()->create(['target_id' => $book->id, 'target_type' => Book::class]);
        }

        // Gán giảm giá cho một tác giả
        $author = Author::first();
        $discount->targets()->create(['target_id' => $author->id, 'target_type' => Author::class]);

        // Gán giảm giá cho một nhà xuất bản
        $publisher = Publisher::first();
        $discount->targets()->create(['target_id' => $publisher->id, 'target_type' => Publisher::class]);
    }
}
