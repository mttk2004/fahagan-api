<?php

namespace Database\Factories;


use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


class OrderFactory extends Factory
{
	protected $model = Order::class;

	public function definition(): array
	{
		return [
			'status' => $this->faker->word(),
			'total_amount' => $this->faker->randomFloat(),
			'shopping_name' => $this->faker->name(),
			'shopping_phone' => $this->faker->phoneNumber(),
			'shopping_city' => $this->faker->city(),
			'shopping_ward' => $this->faker->word(),
			'shopping_address_line' => $this->faker->address(),
			'ordered_at' => Carbon::now(),
			'approved_at' => Carbon::now(),
			'canceled_at' => Carbon::now(),
			'delivered_at' => Carbon::now(),
			'returned_at' => Carbon::now(),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),

			'user_id' => User::factory(),
		];
	}
}
