<?php

namespace Database\Seeders;


use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Seeder;


class BooksSeeder extends Seeder
{
	public function run(): void
	{
		Book::factory(200)->create();

		$books = Book::all();
		$genres = Genre::all();

		$books->each(function(Book $book) use ($genres) {
			$book->genres()->attach(
				$genres->random(rand(1, 2))->pluck('id')->toArray()
			);
		});
	}
}
