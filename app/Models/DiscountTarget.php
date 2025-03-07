<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class DiscountTarget extends Model
{
	public $timestamps = false;
	protected $fillable
		= [
			'discount_id',
			'target_type',
			'target_id',
		];

	public function discount(): BelongsTo
	{
		return $this->belongsTo(Discount::class);
	}
}
