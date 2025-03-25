<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CartItem extends Model
{
	public $timestamps = false;
	public $incrementing = false;
	protected $primaryKey = null;
	protected $fillable
		= [
			'user_id',
			'book_id',
			'quantity',
		];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function book(): BelongsTo
	{
		return $this->belongsTo(Book::class);
	}
}
