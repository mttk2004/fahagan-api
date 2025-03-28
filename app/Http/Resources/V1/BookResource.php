<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Book
 * @property mixed $id
 * @property mixed $title
 * @property mixed $price
 * @property mixed $edition
 * @property mixed $publication_date
 * @property mixed $pages
 * @property mixed $image_url
 * @property mixed $available_count
 * @property mixed $sold_count
 * @property mixed $description
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $deleted_at
 * @property mixed $authors
 * @property mixed $genres
 * @property mixed $publisher
 */
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
                'image_url' => $this->image_url,
                'publication_date' => $this->publication_date,
                'sold_count' => $this->sold_count,
                $this->mergeWhen($request->routeIs('books.show', 'books.store'), [
                    'description' => $this->description,
                    'pages' => $this->pages,
                    'available_count' => $this->available_count,
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
                ]
            ),
            'links' => [
                'self' => route('books.show', ['book' => $this->id]),
            ],
        ];
    }
}
