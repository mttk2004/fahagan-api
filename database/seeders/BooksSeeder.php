<?php

namespace Database\Seeders;


use App\Models\Book;
use Illuminate\Database\Seeder;


class BooksSeeder extends Seeder
{
	public function run(): void
	{
		Book::factory(100)->create();
	}
}
