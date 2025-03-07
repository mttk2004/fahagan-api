<?php

namespace Database\Seeders;


use App\Models\Genre;
use Illuminate\Database\Seeder;


class GenresSeeder extends Seeder
{
	public function run(): void
	{
		$genres = [
			'Văn học',
			'Lịch sử',
			'Triết học',
			'Kinh tế',
			'Tâm lý - Kỹ năng sống',
			'Hồi ký - Tiểu sử'
		];

		foreach ($genres as $genre) {
			Genre::create([
				'name' => $genre,
				'description' => fake()->paragraph()
			]);
		}
	}
}
