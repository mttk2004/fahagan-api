<?php

namespace App\Models;

use App\Interfaces\Discountable;
use App\Interfaces\HasBookRelations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * @method static findOrFail($book_id)
 * @method static create(mixed $bookData)
 */
class Book extends Model implements HasBookRelations, Discountable
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

    public function discounts(): MorphMany
    {
        return $this->morphMany(DiscountTarget::class, 'target');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    /**
     * Lấy tất cả giảm giá hợp lệ của sách (trực tiếp và gián tiếp)
     */
    public function getAllActiveDiscounts(): Collection
    {
        $now = Carbon::now();
        $discounts = collect(); // Tạo Collection rỗng để chứa danh sách giảm giá

        // Lấy giảm giá trực tiếp của sách
        $discounts = $discounts->merge($this->getActiveDiscounts($this->discounts(), $now));

        // Kiểm tra giảm giá từ Author
        if ($this->author) {
            $discounts = $discounts->merge($this->getActiveDiscounts(
                $this->author->discounts(),
                $now
            ));
        }

        // Kiểm tra giảm giá từ Publisher
        if ($this->publisher) {
            $discounts = $discounts->merge($this->getActiveDiscounts(
                $this->publisher->discounts(),
                $now
            ));
        }

        // Kiểm tra giảm giá từ tất cả Genres mà sách thuộc về
        foreach ($this->genres as $genre) {
            $discounts = $discounts->merge($this->getActiveDiscounts($genre->discounts(), $now));
        }

        return $discounts->unique('id'); // Loại bỏ trùng lặp nếu có
    }

    /**
     * Tìm giảm giá cao nhất để áp dụng
     */
    public function getBestDiscount(): ?Discount
    {
        return $this->getAllActiveDiscounts()->sortByDesc(function ($discount) {
            return $discount->discount_type === 'percent'
                ? $discount->discount_value
                : $discount->discount_value / $this->price; // Chuyển fixed discount thành tỷ lệ %
        })->first();
    }

    /**
     * Lấy giảm giá hợp lệ từ một nguồn cụ thể
     */
    public function getActiveDiscounts($query, $now): Collection
    {
        return $query->whereHas('discount', function ($query) use ($now) {
            $query->where('start_date', '<=', $now)
                  ->where('end_date', '>=', $now);
        })
                     ->with('discount')
                     ->get()
                     ->pluck('discount');
    }

    public function usersWithBookInCart(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cart_items', 'book_id', 'user_id')
                    ->withPivot('quantity');
    }
}
