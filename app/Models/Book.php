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
 * @method static find($book_id)
 * @method getAllActiveDiscounts()
 * @property mixed $price
 * @property mixed $id
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
        $activeDiscounts = $this->getActiveDiscounts()->filter(function ($discount) {
            // Lọc các discount không đáp ứng điều kiện min_purchase_amount
            if ($discount->min_purchase_amount > 0 && $this->price < $discount->min_purchase_amount) {
                return false;
            }

            return true;
        });

        if ($activeDiscounts->isEmpty()) {
            return null;
        }

        return $activeDiscounts->sortByDesc(function ($discount) {
            if ($discount->discount_type === 'percentage') {
                // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
                $discountAmount = ($this->price * $discount->discount_value) / 100;
                $discountValue = $discount->max_discount_amount
                  ? min($discountAmount, $discount->max_discount_amount)
                  : $discountAmount;

                return $discountValue / $this->price * 100; // Chuyển thành tương đương %
            } else { // fixed
                // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
                $discountValue = $discount->max_discount_amount
                  ? min($discount->discount_value, $discount->max_discount_amount)
                  : $discount->discount_value;

                return $discountValue / $this->price * 100; // Chuyển thành tương đương %
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
            $discountValue = $bestDiscount->max_discount_amount
              ? min($bestDiscount->discount_value, $bestDiscount->max_discount_amount)
              : $bestDiscount->discount_value;

            return max(0, $originalPrice - $discountValue);
        } else { // percentage
            $discountAmount = ($originalPrice * $bestDiscount->discount_value) / 100;
            $discountValue = $bestDiscount->max_discount_amount
              ? min($discountAmount, $bestDiscount->max_discount_amount)
              : $discountAmount;

            return max(0, $originalPrice - $discountValue);
        }
    }

    public function usersWithBookInCart(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cart_items', 'book_id', 'user_id')
            ->withPivot('quantity');
    }
}
