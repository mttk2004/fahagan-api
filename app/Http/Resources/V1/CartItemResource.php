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
			'attributes' => [
				'quantity' => $this->quantity,
			],
			'relationships' => $this->when(
				$request->routeIs('cart.*'),
				[
					'book' => new BookResource($this->book),
				]
			),
		];
	}
}
