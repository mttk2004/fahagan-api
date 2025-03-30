<?php

namespace Database\Factories;

use App\Constants\AddressNames;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $fakeCreatedAt = fake()->dateTimeBetween('-1 day');

        return [
            'status' => 'pending',
            'total_amount' => 0.0,
            'shopping_name' => fake('vi_VN')->name(),
            'shopping_phone' => fake()->regexify('0[35789][0-9]{8}'),
            'shopping_city' => AddressNames::CITY_NAMES[array_rand(AddressNames::CITY_NAMES)],
            'shopping_ward' => AddressNames::WARD_NAMES[array_rand(AddressNames::WARD_NAMES)],
            'shopping_address_line' => fake('vi_VN')->streetAddress(),
            'ordered_at' => $fakeCreatedAt,
            'approved_at' => null,
            'canceled_at' => null,
            'delivered_at' => null,
            'returned_at' => null,
            'created_at' => $fakeCreatedAt,
            'updated_at' => null,

            'user_id' => User::factory(),
        ];
    }
}
