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
			'id' => $this->id,
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'phone' => $this->phone,
			'email' => $this->email,
			'password' => $this->password,
			'is_customer' => $this->is_customer,
			'last_login' => $this->last_login,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'deleted_at' => $this->deleted_at,
			'notifications_count' => $this->notifications_count,
			'permissions_count' => $this->permissions_count,
			'read_notifications_count' => $this->read_notifications_count,
			'roles_count' => $this->roles_count,
			'tokens_count' => $this->tokens_count,
			'unread_notifications_count' => $this->unread_notifications_count,
		];
	}
}
