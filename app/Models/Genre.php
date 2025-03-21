<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * @method static findOrFail($genre_id)
 */
class Genre extends Model
{
	public $timestamps = false;
	protected $fillable
		= [
			'name',
			'description',
		];

	public function books(): BelongsToMany
	{
		return $this->belongsToMany(Book::class, 'book_genre');
	}

	public function discounts(): MorphMany
	{
		return $this->morphMany(DiscountTarget::class, 'target');
	}
}
