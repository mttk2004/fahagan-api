<?php

namespace App\Http\Resources\V1;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Author
 * @property mixed $id
 * @property mixed $name
 * @property mixed $biography
 * @property mixed $books
 * @property mixed $image_url
 */
class AuthorResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'author',
      'id' => $this->id,
      'attributes' => [
        'name' => $this->name,
        'image_url' => $this->image_url,
        'biography' => $this->when(
          $request->routeIs('authors.*'),
          $this->biography
        ),
      ],
      'relationships' => $this->when(
        $request->routeIs('authors.show', 'authors.store'),
        [
          'books' => new BookCollection($this->writtenBooks),
        ]
      )
    ];
  }
}
