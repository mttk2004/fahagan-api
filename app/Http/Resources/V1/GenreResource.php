<?php

namespace App\Http\Resources\V1;


use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin Genre */
class GenreResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'genre',
			'id' => $this->id,
			'attributes' => [
				'name' => $this->name,
				'description' => $this->description,
			],
			'relationships' => $this->when(
				$request->routeIs('genres.show'),
				[
					'books' => new BookCollection($this->books),
				]
			),
			'links' => [
				'self' => route('genres.show', ['genre' => $this->id]),
			],
		];
	}
}
