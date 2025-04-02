<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $fakePublicationDate = fake()->dateTimeBetween('-10 years');
        $fakeCreatedAt = fake()->dateTimeBetween($fakePublicationDate);

        return [
            'title' => ucfirst(
                fake()->words(
                    fake()->numberBetween(3, 10),
                    asText: true
                )
            ),
            'description' => fake('vi_VN')->paragraphs(
                fake()->numberBetween(5, 8),
                true
            ),
            'price' => fake()->randomFloat(0, 48, 144) * 10000,
            'edition' => fake()->numberBetween(1, 6),
            'pages' => fake()->numberBetween(234, 876),
            'publication_date' => $fakePublicationDate->format('d-m-Y'),
            'sold_count' => fake()->numberBetween(9, 999),
            'created_at' => $fakeCreatedAt,
            'updated_at' => null,

            'publisher_id' => Publisher::inRandomOrder()->first()->id,
        ];
    }
}
