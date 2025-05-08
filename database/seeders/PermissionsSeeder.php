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
          ['name' => 'restore_genres'],

          ['name' => 'view_discounts'],
          ['name' => 'create_discounts'],
          ['name' => 'edit_discounts'],
          ['name' => 'delete_discounts'],
          ['name' => 'restore_discounts'],

          ['name' => 'view_suppliers'],
          ['name' => 'create_suppliers'],
          ['name' => 'edit_suppliers'],
          ['name' => 'delete_suppliers'],
          ['name' => 'restore_suppliers'],

          ['name' => 'view_stock_imports'],
          ['name' => 'create_stock_imports'],
          ['name' => 'edit_stock_imports'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
