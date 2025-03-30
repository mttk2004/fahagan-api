<?php

namespace Database\Seeders;

use App\Models\StockImport;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class StockImportsSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::all()->each(function (Supplier $supplier) {
            $stockImports = StockImport::factory(fake()->numberBetween(1, 2))->create([
                'supplier_id' => $supplier->id,
            ]);

            $stockImports->each(function (StockImport $stockImport) use ($supplier) {
                $books = $supplier->suppliedBooks()->inRandomOrder()
                                  ->take(fake()->numberBetween(1, $supplier->suppliedBooks()->count()))
                                  ->get();

                $totalCost = 0.0;

                $books->each(function ($book) use ($stockImport, &$totalCost) {
                    $quantity = fake()->numberBetween(10, 50) * 10;

                    $stockImport->stockImportItems()->create([
                        'book_id' => $book->id,
                        'quantity' => $quantity,
                        'unit_price' => $book->price,
                    ]);

                    $totalCost += $book->price * $quantity;
                });

                $stockImport->update(['total_cost' => $totalCost]);
            });
        });
    }
}
