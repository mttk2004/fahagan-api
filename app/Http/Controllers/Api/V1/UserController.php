<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use App\Traits\ApiResponses;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Auth;
use Hash;
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
	 *
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
	 * @param $user_id
	 *
	 * @return UserResource|JsonResponse
	 * @group Users
	 */
	public function show($user_id)
	{
		try {
			return new UserResource(User::findOrFail($user_id));
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_USER->value);
		}
	}

	/**
	 * Update a user
	 *
	 * @param UserUpdateRequest $request
	 * @param                   $user_id
	 *
	 * @return JsonResponse
	 * @group Users
	 */
	public function update(UserUpdateRequest $request, $user_id)
	{
		try {
			$user = User::findOrFail($user_id);
			$userData = $request->validated()['data']['attributes'];

			$user->update($userData);

			return $this->ok(ResponseMessage::UPDATED_USER->value, [
				'user' => new UserResource($user),
			]);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_USER->value);
		}
	}

	/**
	 * Delete a user
	 *
	 * @param         $user_id
	 *
	 * @return JsonResponse
	 * @group Users
	 */
	public function destroy($user_id)
	{
		if (!AuthUtils::userCan('delete_users')
			|| AuthUtils::user()->id != $user_id) {
			return ResponseUtils::forbidden();
		}

		try {
			User::findOrFail($user_id)->delete();

			return $this->ok(ResponseMessage::DELETED_USER->value);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_USER->value);
		}
	}

	/**
	 * Change password
	 *
	 * @param ChangePasswordRequest $request
	 *
	 * @return JsonResponse
	 * @group Users
	 */
	public function changePassword(ChangePasswordRequest $request)
	{
		$user = Auth::guard('sanctum')->user();

		if (!$user) {
			return $this->unauthorized();
		}

		$validatedData = $request->validated();

		// Check if the old password is correct
		if (!Hash::check($validatedData['old_password'], $user->password)) {
			return $this->error('Mật khẩu cũ không chính xác.', 422);
		}

		$user->update([
			'password' => bcrypt($validatedData['new_password']),
		]);

		return $this->ok(ResponseMessage::CHANGE_PASSWORD_SUCCESS->value);
	}
}
