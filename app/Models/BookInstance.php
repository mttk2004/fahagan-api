<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class BookInstance extends Model
{
    public $incrementing = false;  // Vô hiệu hóa tự động tăng ID

    protected $keyType = 'string'; // Kiểu khóa chính là string

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = App::make('snowflake')->id();
        });
    }

    protected $fillable
        = [
            'book_id',
            'stock_import_item_id',
            'order_item_id',
            'status',
            'imported_at',
            'sold_at',
            'returned_at',
        ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function stockImportItem(): BelongsTo
    {
        return $this->belongsTo(StockImport::class, 'stock_import_item_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
            'sold_at' => 'datetime',
            'returned_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
