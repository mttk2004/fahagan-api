<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
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
    'delivered_at',
    'completed_at',
    'canceled_at',
  ];

  public function customer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'customer_id');
  }

  public function employee(): BelongsTo|null
  {
    return $this->belongsTo(User::class, 'employee_id') ?? null;
  }

  public function payment(): HasOne|null
  {
    return $this->hasOne(Payment::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  /**
   * Lấy tất cả các mã giảm giá hợp lệ cho đơn hàng (loại order)
   */
  public function getActiveDiscounts(): Collection
  {
    $now = Carbon::now();

    return Discount::where('target_type', 'order')
      ->where('is_active', true)
      ->where('start_date', '<=', $now)
      ->where('end_date', '>=', $now)
      ->get();
  }

  /**
   * Tìm giảm giá tốt nhất để áp dụng cho đơn hàng
   */
  public function getBestDiscount(): ?Discount
  {
    $totalAmount = $this->getTotalAmount();

    return $this->getActiveDiscounts()->sortByDesc(function ($discount) use ($totalAmount) {
      if ($discount->discount_type === 'percentage') {
        return $discount->discount_value;
      } else { // fixed
        return $discount->discount_value / $totalAmount * 100; // Chuyển thành tương đương %
      }
    })->first();
  }

  /**
   * Tính giá trị giảm giá tốt nhất cho đơn hàng
   */
  public function getDiscountedValue(): float
  {
    $totalAmount = $this->getTotalAmount();
    $bestDiscount = $this->getBestDiscount();

    if (! $bestDiscount) {
      return 0;
    }

    if ($bestDiscount->discount_type === 'fixed') {
      return min($totalAmount, $bestDiscount->discount_value);
    } else { // percentage
      return min($totalAmount, $totalAmount * $bestDiscount->discount_value / 100);
    }
  }

  public function getTotalAmount(): float
  {
    return $this->items->sum(function ($item) {
      return ($item->price_at_time - $item->discount_value) * $item->quantity;
    });
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
}
