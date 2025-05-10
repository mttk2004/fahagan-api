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
 * @property mixed $writtenBooks
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
            'biography' => $this->biography,
          ],
          'relationships' => [
            'books' => BookCollection::make($this->whenLoaded('writtenBooks'))->isDirectResponse(false),
          ],
        ];
    }
}
