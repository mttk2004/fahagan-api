<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

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
    protected $fillable = [
        'first_name',
		'last_name',
		'phone',
        'email',
        'password',
		'is_customer',
		'last_login'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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
			'deleted_at' => 'datetime'
        ];
    }
}
