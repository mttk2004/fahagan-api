<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
  public function run(): void
  {
    // Tạo roles
    $admin = Role::create(['name' => 'Admin']);
    $warehouseStaff = Role::create(['name' => 'Warehouse Staff']);
    $salesStaff = Role::create(['name' => 'Sales Staff']);

    $admin->syncPermissions([
      'view_orders',
      'edit_orders',
      'create_books',
      'edit_books',
      'delete_books',
      'view_users',
      'create_users',
      'edit_users',
      'delete_users',
      'create_authors',
      'edit_authors',
      'delete_authors',
      'create_publishers',
      'edit_publishers',
      'delete_publishers',
      'create_genres',
      'edit_genres',
      'delete_genres',
      'view_discounts',
      'create_discounts',
      'edit_discounts',
      'delete_discounts',
      'view_suppliers',
      'create_suppliers',
      'edit_suppliers',
      'delete_suppliers',
      'view_stock_imports',
      'create_stock_imports',
      'edit_stock_imports',
    ]);

    $warehouseStaff->syncPermissions([
      'view_suppliers',
      'view_stock_imports',
      'create_stock_imports',
      'edit_stock_imports',
    ]);

    $salesStaff->syncPermissions([
      'view_orders',
      'edit_orders',
      'view_users',
      'view_discounts',
    ]);
  }
}
