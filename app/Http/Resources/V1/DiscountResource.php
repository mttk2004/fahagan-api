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
			'type' => 'discount',
			'id' => $this->id,
			'attributes' => [
				'name' => $this->name,
				'discount_type' => $this->discount_type,
				'discount_value' => $this->discount_value,
				'start_date' => $this->start_date,
				'end_date' => $this->end_date,
				$this->mergeWhen($request->routeIs('discounts.*'), [
					'created_at' => $this->created_at,
					'updated_at' => $this->updated_at,
					'deleted_at' => $this->deleted_at,
				]),
			],
			'relationships' => $this->when(
				$request->routeIs('discounts.*'),
				[
					'targets' => $this->targets->map(function($target) {
							if ($target->target_type === 'App\Models\Book') {
								return new BookResource($target->target);
							} elseif ($target->target_type === 'App\Models\Author') {
								return new AuthorResource($target->target);
							} elseif ($target->target_type === 'App\Models\Publisher') {
								return new PublisherResource($target->target);
							} elseif ($target->target_type === 'App\Models\Genre') {
								return new GenreResource($target->target);
							}

							return null;
						}),
				]),
			'links' => [
				'self' => route('discounts.show', ['discount' => $this->id]),
			],
		];
	}
}
