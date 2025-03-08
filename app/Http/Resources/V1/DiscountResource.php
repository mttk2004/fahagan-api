<?php

namespace App\Http\Resources\V1;


use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin Discount */
class DiscountResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'discounts',
			'id' => $this->id,
			'attributes' => [
				'name' => $this->name,
				'discount_type' => $this->discount_type,
				'discount_value' => $this->discount_value,
				'start_date' => $this->start_date,
				'end_date' => $this->end_date,
				'created_at' => $this->created_at,
				'updated_at' => $this->updated_at,
			],
			'relationships' => $this->when(
				$request->routeIs('discounts.*'),
				[
					'books' => 'todo',
				]),
			'links' => [
				'self' => route('discounts.show', ['discount' => $this->id]),
			],
		];
	}
}
