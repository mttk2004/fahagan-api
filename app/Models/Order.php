<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Order extends Model
{
    use HasFactory;

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
          'customer_id',
          'employee_id',
          'status',
          'shopping_name',
          'shopping_phone',
          'shopping_city',
          'shopping_district',
          'shopping_ward',
          'shopping_address_line',
          'ordered_at',
          'approved_at',
          'canceled_at',
          'delivered_at',
        ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee(): BelongsTo|null
    {
        return $this->belongsTo(User::class, 'employee_id') ?? null;
    }

    protected function casts(): array
    {
        return [
          'ordered_at' => 'datetime',
          'approved_at' => 'datetime',
          'canceled_at' => 'datetime',
          'delivered_at' => 'datetime',
          'created_at' => 'datetime',
          'updated_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
