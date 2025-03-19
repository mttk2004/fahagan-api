<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * @method static findOrFail($publisher_id)
 * @method static create(mixed $publisherData)
 */
class Publisher extends Model
{
	use HasFactory;


	public $timestamps = false;
	protected $fillable
		= [
			'name',
			'biography',
		];

	public function books(): HasMany
	{
		return $this->hasMany(Book::class);
	}

	public function discounts(): MorphMany
	{
		return $this->morphMany(DiscountTarget::class, 'target');
	}
}
