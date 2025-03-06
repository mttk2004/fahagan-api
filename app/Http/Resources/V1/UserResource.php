<?php

namespace App\Http\Resources\V1;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin User */
class UserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'users',
			'id' => $this->id,
			'attributes' => [
				'first_name' => $this->first_name,
				'last_name' => $this->last_name,
				'email' => $this->email,
				'is_customer' => $this->is_customer,
				$this->mergeWhen($request->routeIs('users.*'), [
					'full_name' => $this->full_name,
					'phone' => $this->phone,
					'last_login' => $this->last_login,
					'created_at' => $this->created_at,
					'updated_at' => $this->updated_at,
					'deleted_at' => $this->deleted_at,
				]),
			],
			'relationships' => $this->when(
				$request->routeIs('users.*'),
				[
					'cart_items' => '',
				]
			),
			'links' => [
				'self' => route('users.show', ['user' => $this->id]),
			],
		];
	}
}
