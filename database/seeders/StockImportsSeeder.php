<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\StockImport;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class StockImportsSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::all()->each(function (Supplier $supplier) {
            // Mỗi nhà cung cấp có 1 hoặc 2 lần nhập hàng
            $stockImports = StockImport::factory(fake()->numberBetween(1, 2))->create([
              'supplier_id' => $supplier->id,
            ]);

            $stockImports->each(function (StockImport $stockImport) use ($supplier) {
                // Mỗi lần nhập hàng có 1 hoặc nhiều cuốn sách thuộc danh sách sách của nhà cung cấp
                $books = $supplier->suppliedBooks()->inRandomOrder()
                  ->take(fake()->numberBetween(1, min(3, $supplier->suppliedBooks()->count())))
                  ->get();

                $totalCost = 0.0;

                // Mỗi cuốn sách có số lượng từ 10 đến 50 (giảm số lượng để tránh tạo quá nhiều dữ liệu)
                $books->each(function ($book) use ($stockImport, &$totalCost) {
                    // Giảm số lượng xuống để tránh quá tải khi seed
                    $quantity = fake()->numberBetween(10, 50);

                    // Tạo stock import item
                    $stockImportItem = $stockImport->stockImportItems()->create([
                      'book_id' => $book->id,
                      'quantity' => $quantity,
                      'unit_price' => $book->price,
                    ]);

                    // Cập nhật available_count trong Book
                    Book::findOrFail($book->id)->increment('available_count', $quantity);

                    $totalCost += $book->price * $quantity;
                });

                $stockImport->update(['total_cost' => $totalCost]);
            });
        });
    }
}
