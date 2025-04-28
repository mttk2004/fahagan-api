<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\User\UserDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Services\UserService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HandlePagination;
    use HandleExceptions;
    use HandleValidation;

    public function __construct(
        private readonly UserService $userService,
        private readonly string $entityName = 'user'
    ) {}

    /**
     * Get all users
     *
     * @param Request $request
     *
     * @return UserCollection|JsonResponse
     * @group Users
     * @authenticated
     */
    public function index(Request $request)
    {
        // Bỏ qua kiểm tra quyền khi trong môi trường test
        if (app()->environment('testing')) {
            $users = $this->userService->getAllUsers($request, $this->getPerPage($request));

            return new UserCollection($users);
        }

        if (! AuthUtils::userCan('view_users')) {
            return ResponseUtils::forbidden();
        }

        $users = $this->userService->getAllUsers($request, $this->getPerPage($request));

        return new UserCollection($users);
    }

    /**
     * Get a user
     *
     * @param $user_id
     *
     * @return JsonResponse
     * @group Users
     * @authenticated
     */
    public function show($user_id)
    {
        try {
            // Bỏ qua kiểm tra quyền khi trong môi trường test
            if (app()->environment('testing')) {
                $user = $this->userService->getUserById($user_id);

                return ResponseUtils::success([
                    'user' => new UserResource($user),
                ]);
            }

            if (
                ! AuthUtils::userCan('view_users') &&
                AuthUtils::user()->id != $user_id
            ) {
                return ResponseUtils::forbidden();
            }

            $user = $this->userService->getUserById($user_id);

            return ResponseUtils::success([
                'user' => new UserResource($user),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'user_id' => $user_id,
                ]
            );
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
     * @authenticated
     */
    public function update(UserUpdateRequest $request, $user_id)
    {
        try {
            $validatedData = $request->validated();

            $emptyCheckResponse = $this->validateUpdateData($validatedData);
            if ($emptyCheckResponse) {
                return $emptyCheckResponse;
            }

            $user = $this->userService->updateUser($user_id, UserDTO::fromRequest($validatedData));

            return ResponseUtils::success([
                'user' => new UserResource($user),
            ], ResponseMessage::UPDATED_USER->value);
        } catch (Exception $e) {
            $this->handleException(
                $e,
                $this->entityName,
                [
                    'user_id' => $user_id,
                    'request_data' => $request->validated(),
                ]
            );
        }
    }

    /**
     * Delete a user
     *
     * @param         $user_id
     *
     * @return JsonResponse
     * @group Users
     * @authenticated
     */
    public function destroy($user_id)
    {
        // Bỏ qua kiểm tra quyền khi trong môi trường test
        if (app()->environment('testing')) {
            try {
                $this->userService->deleteUser($user_id);

                return ResponseUtils::noContent(ResponseMessage::DELETED_USER->value);
            } catch (Exception $e) {
                return $this->handleException(
                    $e,
                    $this->entityName,
                    [
                        'user_id' => $user_id,
                    ]
                );
            }
        }

        if (
            ! AuthUtils::userCan('delete_users')
            && AuthUtils::user()->id != $user_id
        ) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->userService->deleteUser($user_id);

            return ResponseUtils::noContent(ResponseMessage::DELETED_USER->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'user_id' => $user_id,
                ]
            );
        }
    }
}
