<?php

namespace App\Http\Resources\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order
 * @property mixed $id
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
          ],
          'relationships' => [
            'book' => new BookResource($this->book),
          ],
        ];
    }
}
