<?php

namespace App\Http\Sorts\V1;


class DiscountSort extends Sort
{
	protected array $sortableColumns = [
		'name',
		'discount_type',
		'discount_value',
		'start_date',
		'end_date'
	];
}
