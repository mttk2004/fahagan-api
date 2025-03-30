<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class Order extends Model
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
            'user_id',
            'status',
            'total_amount',
            'shopping_name',
            'shopping_phone',
            'shopping_city',
            'shopping_ward',
            'shopping_address_line',
            'ordered_at',
            'approved_at',
            'canceled_at',
            'delivered_at',
            'returned_at',
        ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'ordered_at' => 'timestamp',
            'approved_at' => 'timestamp',
            'canceled_at' => 'timestamp',
            'delivered_at' => 'timestamp',
            'returned_at' => 'timestamp',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }
}
