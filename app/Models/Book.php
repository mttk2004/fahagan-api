<?php

namespace App\Models;

use App\Interfaces\HasBookRelations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * @method static findOrFail($book_id)
 * @method static create(mixed $bookData)
 */
class Book extends Model implements HasBookRelations
{
  use HasFactory;
  use SoftDeletes;

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
    'title',
    'description',
    'price',
    'edition',
    'pages',
    'publication_date',
    'image_url',
    'sold_count',
    'available_count',
    'publisher_id',
  ];

  protected function casts(): array
  {
    return [
      'publication_date' => 'date',
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'deleted_at' => 'datetime',
    ];
  }

  public function authors(): BelongsToMany
  {
    return $this->belongsToMany(Author::class, 'author_book');
  }

  public function publisher(): BelongsTo
  {
    return $this->belongsTo(Publisher::class);
  }

  public function genres(): BelongsToMany
  {
    return $this->belongsToMany(Genre::class, 'book_genre');
  }

  /**
   * Lấy tất cả các mã giảm giá áp dụng trực tiếp cho sách
   */
  public function discounts(): BelongsToMany
  {
    return $this->belongsToMany(Discount::class, 'discount_targets', 'target_id', 'discount_id');
  }

  public function suppliers(): BelongsToMany
  {
    return $this->belongsToMany(Supplier::class);
  }

  /**
   * Lấy tất cả giảm giá hợp lệ của sách
   */
  public function getActiveDiscounts(): Collection
  {
    $now = Carbon::now();

    // Lấy các discount trực tiếp liên kết với sách này
    $directDiscounts = $this->discounts()
      ->where('target_type', 'book')
      ->where('is_active', true)
      ->where('start_date', '<=', $now)
      ->where('end_date', '>=', $now)
      ->get();

    // Lấy tất cả discount áp dụng cho tất cả sách (không cần liên kết qua bảng discount_targets)
    $globalBookDiscounts = Discount::where('target_type', 'book')
      ->whereDoesntHave('targets')
      ->where('is_active', true)
      ->where('start_date', '<=', $now)
      ->where('end_date', '>=', $now)
      ->get();

    // Kết hợp hai tập hợp mã giảm giá
    return $directDiscounts->concat($globalBookDiscounts);
  }

  /**
   * Tìm giảm giá tốt nhất để áp dụng
   */
  public function getBestDiscount(): ?Discount
  {
    return $this->getActiveDiscounts()->sortByDesc(function ($discount) {
      if ($discount->discount_type === 'percentage') {
        return $discount->discount_value;
      } else { // fixed
        return $discount->discount_value / $this->price * 100; // Chuyển thành tương đương %
      }
    })->first();
  }

  /**
   * Tính giá sau khi áp dụng giảm giá tốt nhất
   */
  public function getDiscountedPrice(): float
  {
    $originalPrice = $this->price;
    $bestDiscount = $this->getBestDiscount();

    if (! $bestDiscount) {
      return $originalPrice;
    }

    if ($bestDiscount->discount_type === 'fixed') {
      return max(0, $originalPrice - $bestDiscount->discount_value);
    } else { // percentage
      $discountAmount = ($originalPrice * $bestDiscount->discount_value) / 100;

      return max(0, $originalPrice - $discountAmount);
    }
  }

  public function usersWithBookInCart(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'cart_items', 'book_id', 'user_id')
      ->withPivot('quantity');
  }
}
