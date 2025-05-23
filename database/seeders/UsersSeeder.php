<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // 50 customers
        User::factory(50)->has(
            Address::factory()->count(fake()->numberBetween(1, 3))
        )->create();

        // 1 admin
        $admin = User::create([
          'first_name' => 'Phạm',
          'last_name' => 'Thị Hổm',
          'phone' => '0123456789',
          'email' => 'admin@example.com',
          'password' => bcrypt('password'),
          'is_customer' => false,
        ]);

        // 1 warehouse staff
        $warehouseStaff = User::create([
          'first_name' => 'Tèo',
          'last_name' => 'Nguyễn Văn',
          'phone' => '0938905773',
          'email' => 'warehouse@example.com',
          'password' => bcrypt('password'),
          'is_customer' => false,
        ]);

        // 1 sales staff
        $salesStaff = User::create([
          'first_name' => 'Tủn',
          'last_name' => 'Cao Thị',
          'phone' => '0321424222',
          'email' => 'sales@example.com',
          'password' => bcrypt('password'),
          'is_customer' => false,
        ]);

        $admin->assignRole('Admin');
        $warehouseStaff->assignRole('Warehouse Staff');
        $salesStaff->assignRole('Sales Staff');
    }
}
