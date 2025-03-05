<?php

namespace App\Http\Resources;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/** @mixin User */
class UserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'type' => 'user',
			'id' => $this->id,
			'data' => [
				'full_name' => $this->full_name,
				'first_name' => $this->first_name,
				'last_name' => $this->last_name,
				'phone' => $this->phone,
				'email' => $this->email,
				$this->mergeWhen($request->routeIs('users.*'), [
					'is_customer' => $this->is_customer,
					'last_login' => $this->last_login,
					'created_at' => $this->created_at,
					'updated_at' => $this->updated_at,
					'deleted_at' => $this->deleted_at,
				]),
			],
			'links' => [
				'self' => route('users.show', ['user' => $this->id]),
			],
		];
	}
}
