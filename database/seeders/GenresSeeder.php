<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            [
                'name' => 'Văn học',
                'description' => fake()->paragraph(),
            ],
            [
                'name' => 'Khoa học',
                'description' => fake()->paragraph(),
            ],
            [
                'name' => 'Kinh doanh',
                'description' => fake()->paragraph(),
            ],
            [
                'name' => 'Tiểu thuyết',
                'description' => fake()->paragraph(),
            ],
            [
                'name' => 'Tâm lý học',
                'description' => fake()->paragraph(),
            ],
        ];

        foreach ($genres as $genre) {
            // Thêm slug dựa trên tên
            $genre['slug'] = Str::slug($genre['name']);

            Genre::create($genre);
        }
    }
}
