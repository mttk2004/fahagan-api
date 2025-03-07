<?php

namespace App\Http\Sorts\V1;


class BookSort extends Sort
{
	protected array $sortableColumns
		= [
			'title',
			'price',
			'publication_date',
			'sold_count',
			'available_count',
			'pages',
			'created_at',
			'updated_at',
		];
}
