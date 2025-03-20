<?php

namespace App\Http\Resources\V1;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin User
 * @property mixed $id
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $email
 * @property mixed $is_customer
 * @property mixed $full_name
 * @property mixed $phone
 * @property mixed $last_login
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $deleted_at
 */
class UserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'user',
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
