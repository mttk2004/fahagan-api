<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\OrderResource;
use App\Services\OrderService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  use HandlePagination;
  use HandleExceptions;
  use HandleValidation;

  public function __construct(
    private readonly OrderService $orderService,
    private readonly string $entityName = 'order'
  ) {}

  /**
   * Get all orders
   *
   * @param Request $request
   *
   * @return OrderCollection|JsonResponse
   * @group Orders
   * @authenticated
   */
  public function index(Request $request)
  {
    if (! AuthUtils::userCan('view_orders')) {
      return ResponseUtils::forbidden();
    }

    $orders = $this->orderService->getAllOrders($request, $this->getPerPage($request));

    return new OrderCollection($orders);
  }

    /**
     * Get order by ID
     * @param int $order_id
     *
     * @return JsonResponse
     * @group Orders
     * @unauthenticated
     */
  public function show(int $order_id): JsonResponse
  {
    if (! AuthUtils::userCan('view_orders')) {
      return ResponseUtils::forbidden();
    }

    try {
      $order = $this->orderService->getOrderById($order_id);
      return ResponseUtils::success([
          'order' => new OrderResource($order),
      ]);
    } catch (Exception $e) {
        return $this->handleException($e, $this->entityName, [
            'order_id' => $order_id,
        ]);
    }
  }
}
