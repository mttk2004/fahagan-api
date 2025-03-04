<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;


class UsersSeeder extends Seeder
{
	public function run(): void
	{
		// 20 customers
		User::factory(20)->create();

		// 1 admin
		$admin = User::create([
			'first_name' => 'Admin',
			'last_name' => 'Kiet',
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
			'email' => 'teonguyen@example.com',
			'password' => bcrypt('password'),
			'is_customer' => false,
		]);

		// 1 order approver
		$orderApprover = User::create([
			'first_name' => 'Tủn',
			'last_name' => 'Cao Thị',
			'phone' => '0321424222',
			'email' => 'tuncao@example.com',
			'password' => bcrypt('password'),
			'is_customer' => false,
		]);

		$admin->assignRole('admin');
		$warehouseStaff->assignRole('warehouse_staff');
		$orderApprover->assignRole('order_approver');
	}
}
