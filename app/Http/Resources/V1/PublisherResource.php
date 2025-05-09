<?php

namespace App\Http\Resources\V1;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Publisher
 * @property mixed $id
 * @property mixed $name
 * @property mixed $biography
 * @property mixed $books
 * @property mixed $publishedBooks
 */
class PublisherResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'publisher',
      'id' => $this->id,
      'attributes' => [
        'name' => $this->name,
        'biography' => $this->biography,
      ],
      'relationships' => [
        'books' => BookCollection::make($this->whenLoaded('publishedBooks')),
      ],
    ];
  }
}
