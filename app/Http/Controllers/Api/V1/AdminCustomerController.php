<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\UserService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdminCustomerController extends Controller
{
  use HandleExceptions;
  use HandlePagination;
  use HandleValidation;

  public function __construct(
    private readonly CustomerService $customerService,
    private readonly UserService $userService,
    private readonly string $entityName = 'user'
  ) {}

  /**
   * Get all customers
   *
   * @return UserCollection|JsonResponse
   * @group Admin.Customers
   * @authenticated
   */
  public function index(Request $request)
  {
    if (! AuthUtils::userCan('view_users')) {
      return ResponseUtils::forbidden();
    }

    $users = $this->userService->getAllUsers($request, $this->getPerPage($request));

    return new UserCollection($users);
  }

  /**
   * Get all trashed customers
   *
   * @return UserCollection|JsonResponse
   * @group Admin.Customers
   * @authenticated
   */
  public function trashed(Request $request)
  {
    if (! AuthUtils::userCan('view_users')) {
      return ResponseUtils::forbidden();
    }

    $users = $this->userService->getAllUsers($request, $this->getPerPage($request), true);

    return new UserCollection($users);
  }

  /**
   * Get a customer
   *
   * @return JsonResponse
   * @group Admin.Customers
   * @authenticated
   */
  public function show(int $user_id)
  {
    if (
      ! AuthUtils::userCan('view_users') &&
      AuthUtils::user()->id != $user_id
    ) {
      return ResponseUtils::forbidden();
    }

    try {
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
   * Show orders belong to the customer
   */
  public function showOrders(int $user_id)
  {
    if (! AuthUtils::userCan('view_orders')) {
      return ResponseUtils::forbidden();
    }

    try {
      $orders = User::findOrFail($user_id)->orders;

      return new OrderCollection($orders);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'user_id' => $user_id,
        'action' => 'showOrders',
      ]);
    }
  }

  /**
   * Delete a user
   *
   * @return JsonResponse
   * @group Admin.Customers
   * @authenticated
   * @throws Throwable
   */
  public function destroy(int $user_id)
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
