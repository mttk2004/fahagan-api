<?php

namespace Database\Seeders;



use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class PermissionsSeeder extends Seeder
{
	public function run(): void
	{
		$permissions = [
			['name' => 'view_orders'],
			['name' => 'approve_orders'],
			['name' => 'manage_users'],
		];

		foreach ($permissions as $permission) {
			Permission::create($permission);
		}
	}
}
