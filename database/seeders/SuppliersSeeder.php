<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Book;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::factory(10)->create();

        // Each suppliers supplies some books
        $suppliers->each(function ($supplier) {
            $books = Book::inRandomOrder()->take(rand(1, 5))->pluck('id');
            $supplier->suppliedBooks()->attach($books);
        });
    }
}
