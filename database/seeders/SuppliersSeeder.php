<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::factory(10)->create();
    }
}
