<?php

namespace App\Http\Resources\V1;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CartItem
 * @property mixed $id
 * @property mixed $quantity
 * @property mixed $book
 */
class CartItemResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'cart_item',
      'id' => $this->id,
      'attributes' => [
        'quantity' => $this->quantity,
      ],
      'relationships' => [
        'book' => BookResource::make($this->whenLoaded('book')),
      ],
    ];
  }
}
