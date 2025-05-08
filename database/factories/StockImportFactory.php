<?php

namespace Database\Factories;

use App\Models\StockImport;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockImportFactory extends Factory
{
    protected $model = StockImport::class;

    public function definition(): array
    {
        $fakeImportedAt = fake()->dateTimeBetween('-1 month');

        return [
          'discount_value' => 0.0,
          'imported_at' => $fakeImportedAt,
          'created_at' => $fakeImportedAt,
          'updated_at' => null,

          'employee_id' => User::role('Warehouse Staff')->inRandomOrder()->first(),
          'supplier_id' => Supplier::factory(),
        ];
    }
}
