<?php

namespace Database\Factories;


use App\Constants\AddressNames;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


class SupplierFactory extends Factory
{
	protected $model = Supplier::class;

	public function definition(): array
	{
		return [
			'name' => fake('vi_VN')->company(),
			'phone' => fake('vi_VN')->phoneNumber(),
			'email' => fake('vi_VN')->companyEmail(),
			'city' => AddressNames::CITY_NAMES[array_rand(AddressNames::CITY_NAMES)],
			'ward' => AddressNames::WARD_NAMES[array_rand(AddressNames::WARD_NAMES)],
			'address_line' => fake('vi_VN')->streetAddress(),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		];
	}
}
