<?php

namespace Database\Factories;


use App\Constants\AddressNames;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


class AddressFactory extends Factory
{
	protected $model = Address::class;

	public function definition(): array
	{
		return [
			'name' => fake('vi_VN')->name(),
			'phone' => fake()->regexify('0[35789][0-9]{8}'),
			'city' => AddressNames::CITY_NAMES[array_rand(AddressNames::CITY_NAMES)],
			'ward' => AddressNames::WARD_NAMES[array_rand(AddressNames::WARD_NAMES)],
			'address_line' => fake('vi_VN')->streetAddress(),
			'created_at' => Carbon::now(),

			'user_id' => User::factory(),
		];
	}
}
