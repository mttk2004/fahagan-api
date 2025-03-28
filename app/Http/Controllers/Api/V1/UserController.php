<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Sorts\V1\UserSort;
use App\Models\User;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @group Users
     */
    public function index(Request $request)
    {
        $userSort = new UserSort($request);
        $users = $userSort->apply(User::query())->paginate();

        return ResponseUtils::success([
            'users' => new UserCollection($users),
        ]);
    }

    /**
     * Get a user
     *
     * @param $user_id
     *
     * @return JsonResponse
     * @group Users
     */
    public function show($user_id)
    {
        try {
            return ResponseUtils::success([
                'user' => new UserResource(User::findOrFail($user_id)),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
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

            return ResponseUtils::success([
                'user' => new UserResource($user),
            ], ResponseMessage::UPDATED_USER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
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
        if (! AuthUtils::userCan('delete_users')
            || AuthUtils::user()->id != $user_id) {
            return ResponseUtils::forbidden();
        }

        try {
            User::findOrFail($user_id)->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_USER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
        }
    }
}
