<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Order extends Model
{
	protected $fillable
		= [
			'user_id',
			'status',
			'total_amount',
			'shopping_name',
			'shopping_phone',
			'shopping_city',
			'shopping_ward',
			'shopping_address_line',
			'ordered_at',
			'approved_at',
			'canceled_at',
			'delivered_at',
			'returned_at',
		];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	protected function casts(): array
	{
		return [
			'ordered_at' => 'timestamp',
			'approved_at' => 'timestamp',
			'canceled_at' => 'timestamp',
			'delivered_at' => 'timestamp',
			'returned_at' => 'timestamp',
		];
	}
}
