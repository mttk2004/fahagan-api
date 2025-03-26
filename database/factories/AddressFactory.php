<?php

namespace Database\Factories;


use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


class AddressFactory extends Factory
{
	protected $model = Address::class;
	private array $wardNames
		= [
			'Phú Hòa',
			'Phú Thọ',
			'Phú Thạnh',
			'Phú Cường',
			'Phú Lợi',
			'Phú Hưng',
			'Phú Mỹ',
			'Hoa Phú',
			'Hoa Phong',
			'Hoa Lư',
			'Hoa Hiệp',
			'Hoa Thạnh',
			'Hoa An',
			'Hoa Tiến',
			'Hoa Hưng',
			'Hoa Sơn',
			'Xuân Phú',
			'Xuân Thọ',
			'Xuân Thành',
			'Xuân Thới',
			'Xuân Thới Đông',
			'Xuân Thới Sơn',
			'Xuân Thới Thượng',
			'Tân Phú',
			'Tân Thạnh',
			'Tân Thới',
			'Tân Thới Hiệp',
			'Tân Thới Nhì',
			'Tân Thới Trung',
			'Tân Thới Tây',
			'Tân Hưng',
			'Tân Lập',
			'Tân Hiệp',
			'Tân Bình',
			'Tân Phong',
			'Tân Quý',
			'Tân Thành',
			'Tân Trung',
			'Tân Xuân',
			'An Phú',
			'An Thạnh',
			'An Hòa',
			'An Lợi Đông',
			'An Lợi Tây',
			'An Bình',
			'An Cư',
			'An Nghĩa',
			'Từ Phú',
			'Từ Thọ',
			'Từ Thạnh',
			'Từ Cường',
			'Vinh Phú',
			'Vinh Thọ',
			'Vinh Thành',
			'Vinh Thới',
			'Vinh Lợi',
			'Vinh Hưng',
			'Vinh Mỹ',
			'Vinh An',
			'Vinh Tiến',
			'Hải Châu',
			'Hải Thành',
			'Hải Thạnh',
		];

	public function definition(): array
	{
		return [
			'name' => fake('vi_VN')->name(),
			'phone' => fake()->regexify('0[35789][0-9]{8}'),
			'city' => fake('vi_VN')->city(),
			'ward' => $this->wardNames[array_rand($this->wardNames)],
			'address_line' => fake('vi_VN')->streetAddress(),
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),

			'user_id' => User::factory(),
		];
	}
}
