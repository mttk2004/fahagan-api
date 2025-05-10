<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use App\Traits\HandleExceptions;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminUserController extends Controller
{
    use HandleExceptions;

    public function __construct(
        private readonly UserService $userService,
        private readonly string $entityName = 'user'
    ) {
    }

    /**
     * Delete a user
     *
     * @return JsonResponse
     * @group Admin.Users
     * @authenticated
     * @throws Throwable
     */
    public function destroy($user_id)
    {
        if (
            ! AuthUtils::userCan('delete_users')
            && AuthUtils::user()->id != $user_id
            || User::findOrFail($user_id)->hasRole('Admin')
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
