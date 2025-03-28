<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => fake('vi_VN')->name(),
            'biography' => fake()->paragraphs(
                fake()->numberBetween(3, 6),
                true
            ),
        ];
    }
}
