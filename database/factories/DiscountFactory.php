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
    $discountType = fake('vi_VN')->randomElement(['percentage', 'fixed']);
    $targetType = fake('vi_VN')->randomElement(['book', 'order']);
    $startDate = fake('vi_VN')->dateTimeBetween('+1 day', '+30 days');
    $endDate = fake('vi_VN')->dateTimeBetween(
      Carbon::instance($startDate),
      Carbon::instance($startDate)->addDays(60)
    );

    return [
      'id' => Str::random(18),
      'name' => 'Giảm giá ' . fake('vi_VN')->word(),
      'target_type' => $targetType,
      'description' => fake('vi_VN')->sentence(),
      'discount_type' => $discountType,
      'discount_value' => $discountType === 'percentage'
        ? fake('vi_VN')->numberBetween(5, 50)
        : fake('vi_VN')->numberBetween(1, 20) * 10000,
      'start_date' => $startDate,
      'end_date' => $endDate,
    ];
  }
}
