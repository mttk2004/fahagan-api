<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @method static factory()
 */
class Address extends Model
{
	use HasFactory;


	protected $fillable
		= [
			'user_id',
			'name',
			'phone',
			'city',
			'ward',
			'address_line',
		];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
