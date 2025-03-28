<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Seeder;

class AuthorsSeeder extends Seeder
{
    public function run(): void
    {
        Author::factory(10)->create();

        $authors = Author::all();
        $books = Book::all();

        foreach ($books as $book) {
            $book->authors()
                 ->attach($authors->random(rand(1, 3))->pluck('id')->toArray());
        }
    }
}
