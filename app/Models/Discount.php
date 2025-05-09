<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

/**
 * @method static findOrFail($discount_id)
 * @method static create(mixed $discountData)
 * @method static where(string $string, string $name)
 * @method static whereHas(string $string, \Closure $param)
 *
 * @property mixed $id
 */
class Discount extends Model
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
            'name',
            'discount_type',
            'discount_value',
            'target_type',
            'min_purchase_amount',
            'max_discount_amount',
            'start_date',
            'end_date',
            'description',
            'is_active',
        ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function targets(): HasMany
    {
        return $this->hasMany(DiscountTarget::class);
    }

    /**
     * Lấy các sách được áp dụng giảm giá
     */
    public function books()
    {
        return $this->belongsToMany(Book::class, 'discount_targets', 'discount_id', 'target_id');
    }

    /**
     * Kiểm tra xem discount có áp dụng cho order không
     */
    public function isOrderDiscount(): bool
    {
        return $this->target_type === 'order';
    }

    /**
     * Kiểm tra xem discount có áp dụng cho sách không
     */
    public function isBookDiscount(): bool
    {
        return $this->target_type === 'book';
    }

    /**
     * Kiểm tra xem mã giảm giá có hợp lệ không
     */
    public function isValid(): bool
    {
        $now = now();

        return $this->is_active && $now->between($this->start_date, $this->end_date);
    }
}
