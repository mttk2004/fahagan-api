<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Interfaces\HasCart;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static findOrFail($user_id)
 * @method static create(array $array)
 */
class User extends Authenticatable implements HasCart
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable
        = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'password',
            'is_customer',
            'last_login',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden
        = [
            'password',
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_customer' => 'boolean',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name.' '.$this->last_name
        );
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }

    public function scopeEmployees($query)
    {
        return $query->where('is_customer', false);
    }

    /**
     * Lấy tất cả địa chỉ của user
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Lấy tất cả item trong giỏ hàng của user
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Kiểm tra xem sách đã có trong giỏ hàng chưa
     */
    public function isBookInCart($bookId): bool
    {
        return $this->cartItems()->where('book_id', $bookId)->exists();
    }

    /**
     * Lấy danh sách sách trong giỏ hàng thông qua bảng pivot
     */
    public function booksInCart(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'cart_items', 'user_id', 'book_id')
            ->withPivot('quantity');
    }

    /**
     * Lấy thông tin mục giỏ hàng của một sách cụ thể (CartItem)
     */
    public function getCartItemByBook($bookId): ?CartItem
    {
        return $this->cartItems()->where('book_id', $bookId)->first();
    }
}
