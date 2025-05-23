<?php

namespace App\Http\Resources\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order
 * @property mixed $id
 * @property mixed $customer_id
 * @property mixed $employee_id
 * @property mixed $status
 * @property mixed $shopping_name
 * @property mixed $shopping_phone
 * @property mixed $shopping_city
 * @property mixed $shopping_district
 * @property mixed $shopping_ward
 * @property mixed $shopping_address_line
 * @property mixed $ordered_at
 * @property mixed $approved_at
 * @property mixed $canceled_at
 * @property mixed $delivered_at
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $completed_at
 * @property mixed $customer
 * @property mixed $employee
 * @property mixed $payment
 * @property mixed $items
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'order',
          'id' => $this->id,
          'attributes' => [
            'status' => $this->status,
            'shopping_name' => $this->shopping_name,
            'shopping_phone' => $this->shopping_phone,
            'shopping_city' => $this->shopping_city,
            'shopping_district' => $this->shopping_district,
            'shopping_ward' => $this->shopping_ward,
            'shopping_address_line' => $this->shopping_address_line,
            'ordered_at' => $this->ordered_at,
            'approved_at' => $this->approved_at,
            'delivering_at' => $this->delivering_at,
            'delivered_at' => $this->delivered_at,
            'completed_at' => $this->completed_at,
            'canceled_at' => $this->canceled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          ],
          'relationships' => [
            'customer' => UserResource::make($this->whenLoaded('customer')),
            'employee' => UserResource::make($this->whenLoaded('employee')),
            'items' => OrderItemCollection::make($this->whenLoaded('items')),
            'payment' => PaymentResource::make($this->whenLoaded('payment')),
          ],
        ];
    }
}
