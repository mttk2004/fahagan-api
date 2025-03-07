<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Discount extends Model
{
	use SoftDeletes;


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
		];
	}
}
