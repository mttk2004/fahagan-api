<?php

namespace App\Http\Resources\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order
 * @property mixed $id
 * @property mixed $quantity
 * @property mixed $book
 * @property mixed $price_at_time
 * @property mixed $discount_value
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'order_item',
          'id' => $this->id,
          'attributes' => [
            'quantity' => $this->quantity,
            'price_at_time' => $this->price_at_time,
            'discount_value' => $this->discount_value,
          ],
          'relationships' => [
            'book' => BookResource::make($this->whenLoaded('book')),
          ],
        ];
    }
}
