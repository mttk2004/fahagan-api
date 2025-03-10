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
			['name' => 'edit_orders'],

			['name' => 'create_books'],
			['name' => 'edit_books'],
			['name' => 'delete_books'],

			['name' => 'view_users'],
			['name' => 'create_users'],
			['name' => 'edit_users'],
			['name' => 'delete_users'],

			['name' => 'create_authors'],
			['name' => 'edit_authors'],
			['name' => 'delete_authors'],

			['name' => 'create_publishers'],
			['name' => 'edit_publishers'],
			['name' => 'delete_publishers'],

			['name' => 'create_genres'],
			['name' => 'edit_genres'],
			['name' => 'delete_genres'],

			['name' => 'view_discounts'],
			['name' => 'create_discounts'],
			['name' => 'edit_discounts'],
			['name' => 'delete_discounts'],

			['name' => 'view_roles'],
			['name' => 'create_roles'],
			['name' => 'edit_roles'],
			['name' => 'delete_roles'],

			['name' => 'view_permissions'],
			['name' => 'create_permissions'],
			['name' => 'edit_permissions'],
			['name' => 'delete_permissions'],
		];

		foreach ($permissions as $permission) {
			Permission::create($permission);
		}
	}
}
