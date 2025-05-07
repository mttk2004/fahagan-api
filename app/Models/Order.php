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

/**
 * @method static create(array $array)
 */
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
    'delivering_at',
    'delivered_at',
    'completed_at',
    'canceled_at',
  ];

  public function customer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'customer_id');
  }

  public function employee(): ?BelongsTo
  {
    return $this->belongsTo(User::class, 'employee_id') ?? null;
  }

  public function payment(): ?HasOne
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

    $activeDiscounts = $this->getActiveDiscounts()->filter(function ($discount) use ($totalAmount) {
      // Lọc các discount không đáp ứng điều kiện min_purchase_amount
      if ($discount->min_purchase_amount > 0 && $totalAmount < $discount->min_purchase_amount) {
        return false;
      }

      return true;
    });

    if ($activeDiscounts->isEmpty()) {
      return null;
    }

    return $activeDiscounts->sortByDesc(function ($discount) use ($totalAmount) {
      if ($discount->discount_type === 'percentage') {
        // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
        $discountAmount = ($totalAmount * $discount->discount_value) / 100;
        $discountValue = $discount->max_discount_amount
          ? min($discountAmount, $discount->max_discount_amount)
          : $discountAmount;

        return $discountValue / $totalAmount * 100; // Chuyển thành tương đương %
      } else { // fixed
        // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
        $discountValue = $discount->max_discount_amount
          ? min($discount->discount_value, $discount->max_discount_amount)
          : $discount->discount_value;

        return $discountValue / $totalAmount * 100; // Chuyển thành tương đương %
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
      $discountValue = $bestDiscount->max_discount_amount
        ? min($bestDiscount->discount_value, $bestDiscount->max_discount_amount)
        : $bestDiscount->discount_value;

      return min($totalAmount, $discountValue);
    } else { // percentage
      $discountAmount = ($totalAmount * $bestDiscount->discount_value) / 100;
      $discountValue = $bestDiscount->max_discount_amount
        ? min($discountAmount, $bestDiscount->max_discount_amount)
        : $discountAmount;

      return min($totalAmount, $discountValue);
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
      'delivering_at' => 'datetime',
      'delivered_at' => 'datetime',
      'completed_at' => 'datetime',
      'canceled_at' => 'datetime',
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
    ];
  }
}
