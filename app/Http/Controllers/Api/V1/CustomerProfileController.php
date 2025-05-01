<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\User\UserDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserUpdateRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\UserService;
use App\Traits\HandleExceptions;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;

class CustomerProfileController extends Controller
{
  use HandleExceptions;
  use HandleValidation;

  public function __construct(
    private readonly UserService $userService,
    private readonly string $entityName = 'user'
  ) {}

  /**
   * Xem thông tin profile của customer đang đăng nhập
   *
   * @return JsonResponse
   * @group Customer.Profile
   * @authenticated
   */
  public function show()
  {
    try {
      $user = AuthUtils::user();
      if (! $user) {
        return ResponseUtils::unauthorized();
      }

      return ResponseUtils::success([
        'user' => new UserResource($user),
      ]);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName
      );
    }
  }

  /**
   * Cập nhật thông tin profile của customer đang đăng nhập
   *
   * @param UserUpdateRequest $request
   * @return JsonResponse
   * @group Customer.Profile
   * @authenticated
   */
  public function update(UserUpdateRequest $request)
  {
    try {
      $user = AuthUtils::user();
      if (! $user) {
        return ResponseUtils::unauthorized();
      }

      $validatedData = $request->validated();

      $emptyCheckResponse = $this->validateUpdateData($validatedData);
      if ($emptyCheckResponse) {
        return $emptyCheckResponse;
      }

      $updatedUser = $this->userService->updateUser($user->id, UserDTO::fromRequest($validatedData));

      return ResponseUtils::success([
        'user' => new UserResource($updatedUser),
      ], ResponseMessage::UPDATED_USER->value);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName,
        [
          'request_data' => $request->validated(),
        ]
      );
    }
  }

  /**
   * Xóa tài khoản của customer đang đăng nhập
   *
   * @return JsonResponse
   * @group Customer.Profile
   * @authenticated
   */
  public function destroy()
  {
    try {
      $user = AuthUtils::user();
      if (! $user) {
        return ResponseUtils::unauthorized();
      }

      $this->userService->deleteUser($user->id);

      return ResponseUtils::noContent(ResponseMessage::DELETED_USER->value);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName
      );
    }
  }
}
