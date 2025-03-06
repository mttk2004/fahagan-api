<?php

namespace App\Http\Resources\V1;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;


/** @see \App\Models\Book */
class BookCollection extends ResourceCollection
{
	public function toArray(Request $request): array
	{
		return [
			'data' => $this->collection,
			'included' => [],
		];
	}
}
