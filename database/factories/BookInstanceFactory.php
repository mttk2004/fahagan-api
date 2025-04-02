<?php

namespace Database\Factories;

use App\Models\BookInstance;
use App\Models\Book;
use App\Models\StockImportItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BookInstanceFactory extends Factory
{
    protected $model = BookInstance::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::inRandomOrder()->first()->id,
            'stock_import_item_id' => StockImportItem::inRandomOrder()->first()->id ?? 1,
            'status' => 'available', // let's assume all book instances are available
            'imported_at' => Carbon::now(),
        ];
    }
}
