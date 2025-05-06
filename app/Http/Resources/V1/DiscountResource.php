<?php

namespace App\Http\Resources\V1;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Discount
 * @property mixed $id
 * @property mixed $name
 * @property mixed $discount_type
 * @property mixed $discount_value
 * @property mixed $min_purchase_amount
 * @property mixed $max_discount_amount
 * @property mixed $target_type
 * @property mixed $start_date
 * @property mixed $end_date
 * @property mixed $description
 * @property mixed $is_active
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $deleted_at
 * @property mixed $targets
 */
class DiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'discount',
          'id' => $this->id,
          'attributes' => [
            'name' => $this->name,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'target_type' => $this->target_type,
            'min_purchase_amount' => $this->min_purchase_amount,
            'max_discount_amount' => $this->max_discount_amount,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => (bool)$this->is_active,
            $this->mergeWhen($request->routeIs('discounts.show', 'discounts.store', 'discounts.update'), [
              'description' => $this->description,
              'created_at' => $this->created_at,
              'updated_at' => $this->updated_at,
              'deleted_at' => $this->deleted_at,
            ]),
          ],
          'relationships' => $this->when(
              $request->routeIs('discounts.show', 'discounts.store', 'discounts.update') && $this->target_type === 'book',
              [
              'targets' => $this->whenLoaded('targets', function () {
                  return BookResource::collection(
                      $this->targets->map(function ($target) {
                          return $target->book;
                      })
                  );
              }),
        ]
          ),
        ];
    }
}
