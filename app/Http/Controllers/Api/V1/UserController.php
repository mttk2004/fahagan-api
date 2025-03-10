<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class UserController extends Controller
{
	use ApiResponses;


	public function index(Request $request)
	{
		$userSort = new UserSort($request);
		$users = $userSort->apply(User::query())->paginate();

		return new UserCollection($users);
	}

	public function show(User $user)
	{
		return new UserResource($user);
	}

	public function update(Request $request, User $user) {}

	public function destroy(Request $request, $user_id)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_users') || $user->id != $user_id) {
			return $this->error('Bạn không có quyền thực hiện hành động này.', 403);
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
