<?php

namespace App\Http\Resources\V1;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CartItem */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'cart_item',
          'id' => null,
          'attributes' => [
            'quantity' => $this->quantity,
          ],
          'relationships' => [
            'book' => [
              'data' => new BookResource($this->book),
            ],
          ],
          'links' => [
            'self' => route('customer.cart.index'),
          ],
        ];
    }
}
