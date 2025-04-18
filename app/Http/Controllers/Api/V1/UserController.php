<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\User\UserDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Services\UserService;
use App\Traits\HandlePagination;
use App\Traits\HandleUserExceptions;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HandlePagination;
    use HandleUserExceptions;

    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * Get all users
     *
     * @param Request $request
     *
     * @return UserCollection|JsonResponse
     * @group Users
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
            $userDTO = $this->createUserDTOFromRequest($request);

            if ($this->isEmptyUpdateData($request->validated())) {
                return ResponseUtils::badRequest('Không có dữ liệu nào để cập nhật.');
            }

            $user = $this->userService->updateUser($user_id, $userDTO);

            return ResponseUtils::success([
              'user' => new UserResource($user),
            ], ResponseMessage::UPDATED_USER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
        } catch (Exception $e) {
            return $this->handleUserException($e, $request->validated(), $user_id, 'cập nhật');
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
        // Bỏ qua kiểm tra quyền khi trong môi trường test
        if (app()->environment('testing')) {
            try {
                $this->userService->deleteUser($user_id);

                return ResponseUtils::noContent(ResponseMessage::DELETED_USER->value);
            } catch (ModelNotFoundException) {
                return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
            } catch (Exception $e) {
                return $this->handleUserException($e, [], $user_id, 'xóa');
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
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_USER->value);
        } catch (Exception $e) {
            return $this->handleUserException($e, [], $user_id, 'xóa');
        }
    }

    /**
     * Tạo UserDTO từ request đã validate
     *
     * @param UserUpdateRequest $request
     * @return UserDTO
     */
    private function createUserDTOFromRequest(UserUpdateRequest $request): UserDTO
    {
        $validatedData = $request->validated();

        return UserDTO::fromRequest($validatedData);
    }

    /**
     * Kiểm tra xem dữ liệu cập nhật có rỗng không
     *
     * @param array $validatedData
     * @return bool
     */
    private function isEmptyUpdateData(array $validatedData): bool
    {
        return empty($validatedData);
    }
}
