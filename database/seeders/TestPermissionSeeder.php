<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TestPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các quyền cần thiết cho testing
        $permissions = [
            'create_books',
            'edit_books',
            'delete_books',
            'create_publishers',
            'edit_publishers',
            'delete_publishers',
            'create_authors',
            'edit_authors',
            'delete_authors',
            'create_genres',
            'edit_genres',
            'delete_genres',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
