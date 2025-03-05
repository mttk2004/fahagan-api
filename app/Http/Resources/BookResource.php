<?php

namespace App\Http\Resources;


use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin Book */
class BookResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'price' => $this->price,
			'edition' => $this->edition,
			'pages' => $this->pages,
			'publication_date' => $this->publication_date,
			'sold_count' => $this->sold_count,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		];
	}
}
