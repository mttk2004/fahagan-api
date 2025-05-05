<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class Payment extends Model
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
    'order_id',
    'status',
    'method',
    'total_amount',
    'discount_value',
    'transaction_ref',
    'gateway_response',
  ];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  protected function casts(): array
  {
    return [
      'status' => PaymentStatus::class,
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'gateway_response' => 'array',
    ];
  }
}
