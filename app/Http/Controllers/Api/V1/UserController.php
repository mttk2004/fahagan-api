<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class UserController extends Controller
{
	use ApiResponses;


	/**
	 * Get all users
	 *
	 * @param Request $request
	 * @return UserCollection
	 * @group Users
	 */
	public function index(Request $request)
	{
		$userSort = new UserSort($request);
		$users = $userSort->apply(User::query())->paginate();

		return new UserCollection($users);
	}

	/**
	 * Get a user
	 *
	 * @param User $user
	 * @return UserResource
	 * @group Users
	 */
	public function show(User $user)
	{
		return new UserResource($user);
	}

	/**
	 * Update a user
	 *
	 * @param UserUpdateRequest $request
	 * @param $user_id
	 * @return JsonResponse|UserResource
	 * @group Users
	 */
	public function update(UserUpdateRequest $request, $user_id)
	{
		try {
			$user = User::findOrFail($user_id);
			$userData = $request->validated()['data']['attributes'];
			$user->update($userData);

			return new UserResource($user);
		} catch (ModelNotFoundException) {
			return $this->error('Người dùng không tồn tại.', 404);
		}
	}

	/**
	 * Delete a user
	 *
	 * @param Request $request
	 * @param $user_id
	 * @return JsonResponse
	 * @group Users
	 */
	public function destroy(Request $request, $user_id)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_users') || $user->id != $user_id) {
			return $this->forbidden();
		}

		try {
			$userToDelete = User::findOrFail($user_id);
			$userToDelete->delete();

			return $this->ok('Xóa người dùng thành công.');
		} catch (ModelNotFoundException) {
			return $this->error('Người dùng không tồn tại.', 404);
		}
	}
}
