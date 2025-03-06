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
			'links' => [
				'self' => route('books.show', ['book' => $this->id])
			],
			'data' => [
				'type' => 'books',
				'id' => $this->id,
				'attributes' => [
					'title' => $this->title,
					'price' => $this->price,
					'edition' => $this->edition,
					'publication_date' => $this->publication_date,
					'pages' => $this->pages,
					$this->mergeWhen($request->routeIs('books.*'), [
						'description' => $this->description,
						'sold_count' => $this->sold_count,
						'available_count' => $this->available_count,
						'created_at' => $this->created_at,
						'updated_at' => $this->updated_at,
						'deleted_at' => $this->deleted_at,
					]),
				],
				'relationships' => [
					'authors' => [
						'links' => [
//							'self' => route('books.relationships.authors', ['book' => $this->id]),
//							'related' => route('books.authors', ['book' => $this->id]),
						],
						'data' => $this->authors->map(function ($author) {
							return [
								'type' => 'authors',
								'id' => $author->id
							];
						}),
					]
				],
			],
			'included' => []
		];
	}
}
