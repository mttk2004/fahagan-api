<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockImportItem extends Model
{
    public $timestamps = false;

    protected $fillable
        = [
          'stock_import_id',
          'book_id',
          'quantity',
          'unit_price',
        ];

    protected function subTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit_price * $this->quantity
        );
    }

    public function stockImport(): BelongsTo
    {
        return $this->belongsTo(StockImport::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
