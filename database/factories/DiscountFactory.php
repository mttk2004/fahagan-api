<?php

namespace Database\Factories;

use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $discountType = $this->faker->randomElement(['percent', 'fixed']);
        $startDate = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $endDate = $this->faker->dateTimeBetween(
            Carbon::instance($startDate),
            Carbon::instance($startDate)->addDays(60)
        );

        return [
            'id' => Str::random(18),
            'name' => 'Giảm giá ' . $this->faker->word(),
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'percent'
                ? $this->faker->numberBetween(5, 50)
                : $this->faker->numberBetween(1, 20) * 10000,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
