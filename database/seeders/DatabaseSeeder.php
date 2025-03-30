<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            UsersSeeder::class,
            PublishersSeeder::class,
            GenresSeeder::class,
            SuppliersSeeder::class,
            BooksSeeder::class,
            AuthorsSeeder::class,
            DiscountsSeeder::class,
            CartItemsSeeder::class,
            StockImportsSeeder::class,
            OrdersSeeder::class,
        ]);
    }
}
