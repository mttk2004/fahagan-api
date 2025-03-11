<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class WhoAmI extends Controller
{
	use ApiResponses;

	/**
	 * Get the authenticated user
	 *
	 * @param Request $request
	 * @return JsonResponse
	 * @group Auth
	 */
	public function whoAmI(Request $request)
	{
		$user = $request->user();

		return $this->ok('Success', [
			'you_are' => $user->is_customer ? 'Customer' : 'Not a Customer',
			'id' => $user->id,
			'attributes' => [
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'email' => $user->email,
				'phone' => $user->phone,
				'is_customer' => $user->is_customer,
				'last_login' => $user->last_login,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at,
				'deleted_at' => $user->deleted_at,
			],
			'roles' =>  $user->getRoleNames(),
			'permissions' => $user->getAllPermissions()->pluck('name')
		]);
	}
}
