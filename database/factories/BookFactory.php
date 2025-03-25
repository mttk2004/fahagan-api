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
		$fakePublicationDate = $this->faker->dateTimeBetween('-10 years');
		$fakeCreatedAt = $this->faker->dateTimeBetween($fakePublicationDate);

		return [
			'title' => ucfirst(fake()->words(
				fake()->numberBetween(3, 10), asText: true)
			),
			'description' => fake('vi_VN')->paragraphs(
				fake()->numberBetween(5, 8),
				true
			),
			'price' => fake()->randomFloat(0, 48, 144) * 10000,
			'edition' => $this->faker->numberBetween(1, 6),
			'pages' => $this->faker->numberBetween(234, 876),
			'publication_date' => $fakePublicationDate->format('d-m-Y'),
			'available_count' => $this->faker->numberBetween(99, 9999),
			'sold_count' => $this->faker->numberBetween(9, 999),
			'created_at' => $fakeCreatedAt,
			'updated_at' => $fakeCreatedAt,

			'publisher_id' => Publisher::inRandomOrder()->first()->id,
		];
	}
}
