<?php

namespace App\Http\Resources\V1;


use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin Book */
class BookResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'book',
			'id' => $this->id,
			'attributes' => [
				'title' => $this->title,
				'price' => $this->price,
				'edition' => $this->edition,
				'publication_date' => $this->publication_date,
				'pages' => $this->pages,
				'sold_count' => $this->sold_count,
				$this->mergeWhen($request->routeIs('books.show', 'books.store'), [
					'available_count' => $this->available_count,
					'description' => $this->description,
					'created_at' => $this->created_at,
					'updated_at' => $this->updated_at,
					'deleted_at' => $this->deleted_at,
				]),
			],
			'relationships' => $this->when(
				$request->routeIs('books.*'),
				[
					'authors' => new AuthorCollection($this->authors),
					'genres' => new GenreCollection($this->genres),
					'publisher' => new PublisherResource($this->publisher),
				]),
			'links' => [
				'self' => route('books.show', ['book' => $this->id]),
			],
		];
	}
}
