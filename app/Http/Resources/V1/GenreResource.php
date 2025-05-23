<?php

namespace App\Http\Resources\V1;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Genre
 * @property mixed $id
 * @property mixed $name
 * @property mixed $book_count
 * @property mixed $description
 * @property mixed $books
 * @property mixed $slug
 */
class GenreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'genre',
          'id' => $this->id,
          'attributes' => [
            'name' => $this->name,
            'slug' => $this->slug,
            'books_count' => $this->whenLoaded('books', function () {
                return $this->books->count();
            }, 0),
            'description' => $this->description,
          ],
          'relationships' => [
            'books' => BookCollection::make($this->whenLoaded('books'))->isDirectResponse(false),
      ],
        ];
    }
}
