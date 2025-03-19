<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * @method static create(mixed $authorData)
 * @method static findOrFail($author_id)
 */
class Author extends Model
{
	use HasFactory;


	public $timestamps = false;
	protected $fillable
		= [
			'name',
			'biography',
		];

	public function books(): BelongsToMany
	{
		return $this->belongsToMany(Book::class, 'author_book');
	}

	public function discounts(): MorphMany
	{
		return $this->morphMany(DiscountTarget::class, 'target');
	}
}
