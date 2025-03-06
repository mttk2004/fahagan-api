<?php

namespace App\Http\Resources\V1;


use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin Author */
class AuthorResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'authors',
			'id' => $this->id,
			'attributes' => [
				'name' => $this->name,
				'biography' => $this->when(
					$request->routeIs('authors.*'),
					$this->biography
				),
			],
			'relationships' => $this->when(
				$request->routeIs('authors.*'),
				[
					'books' => new BookCollection($this->books),
				]),
			'links' => [
				'self' => route('authors.show', ['author' => $this->id]),
			],
		];
	}
}
