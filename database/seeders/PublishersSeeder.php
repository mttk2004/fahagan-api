<?php

namespace Database\Seeders;


use App\Models\Publisher;
use Illuminate\Database\Seeder;


class PublishersSeeder extends Seeder
{
	public function run(): void
	{
		Publisher::factory(10)->create();
	}
}
