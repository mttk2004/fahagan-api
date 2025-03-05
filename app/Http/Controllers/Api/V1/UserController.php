<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;


class UserController extends Controller
{
	use ApiResponses;

	public function index(Request $request)
	{
		$userSort = new UserSort($request);
		$users = $userSort->apply(User::query())->paginate();

		return UserResource::collection($users);
	}

	public function show(User $user)
	{
		return new UserResource($user);
	}

	public function update(Request $request, User $user) {}

	public function destroy(User $user) {}
}
