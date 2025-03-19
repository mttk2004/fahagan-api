<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


/**
 * @method static findOrFails($discount_id)
 */
class Discount extends Model
{
	use SoftDeletes;


	public $incrementing = false;  // Vô hiệu hóa tự động tăng ID
	protected $keyType = 'string'; // Kiểu khóa chính là string

	protected static function boot(): void
	{
		parent::boot();

		static::creating(function($model) {
			$model->{$model->getKeyName()} = App::make('snowflake')->id();
		});
	}

	protected $fillable
		= [
			'name',
			'discount_type',
			'discount_value',
			'start_date',
			'end_date',
		];

	protected function casts(): array
	{
		return [
			'start_date' => 'datetime',
			'end_date' => 'datetime',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
			'deleted_at' => 'datetime',
		];
	}

	public function targets(): HasMany
	{
		return $this->hasMany(DiscountTarget::class);
	}
}
