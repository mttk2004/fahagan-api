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
 * @property mixed $cartItems
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
            'phone' => $this->phone,
            $this->mergeWhen($request->routeIs('users.*', 'admin.employees.*', 'customer.profile.*'), [
              'full_name' => $this->full_name,
              'last_login' => $this->last_login,
              'created_at' => $this->created_at,
              'updated_at' => $this->updated_at,
              'deleted_at' => $this->deleted_at,
            ]),
            $this->mergeWhen(! $this->is_customer, [
              'roles' => $this->getRoleNames(),
              'permissions' => $this->getAllPermissions()->pluck('name'),
            ]),
          ],
          'relationships' => $this->when(
              ($request->routeIs('users.show', 'users.store', 'users.update', 'admin.employees.show', 'customer.profile.show', 'customer.profile.store', 'customer.profile.update')) && $this->is_customer,
              [
              'cart_items' => CartItemResource::collection($this->cartItems),
        ]
          ),
        ];
    }
}
