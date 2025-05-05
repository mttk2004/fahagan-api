<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CustomerOrderStoreRequest;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\VNPayService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
  use HandleExceptions;
  use HandlePagination;

  public function __construct(
    private readonly OrderService $orderService,
    private readonly VNPayService $vnpayService,
    private readonly string $entityName = 'order'
  ) {}

  /**
   * Get all orders of the authenticated customer.
   *
   * @param Request $request
   * @return JsonResponse|OrderCollection
   * @group Customer.Order
   * @authenticated
   */
  public function index(Request $request)
  {
    $orders = $this->orderService->getCustomerOrders($request, $this->getPerPage($request));

    return new OrderCollection($orders);
  }

  /**
   * Get order of the authenticated customer by ID.
   *
   * @param Order $order
   * @return JsonResponse
   * @group Customer.Order
   * @authenticated
   */
  public function show(Order $order)
  {
    $user = AuthUtils::user();

    // Kiểm tra đơn hàng có thuộc về người dùng hiện tại không
    if ($order->customer_id != $user->id) {
      Log::error('loi forbidden', [
        'user_id' => $user->id,
        'order.customer_id' => $order->customer_id,
      ]);

      return ResponseUtils::forbidden();
    }

    $orderWithDetails = $this->orderService->getOrderDetails($order->id);

    return ResponseUtils::success([
      'order' => new OrderResource($orderWithDetails),
    ]);
  }

  /**
   * Create a new order from the cart of the authenticated customer.
   *
   * @param CustomerOrderStoreRequest $request
   * @return JsonResponse
   * @group Customer.Order
   * @authenticated
   */
  public function store(CustomerOrderStoreRequest $request)
  {
    try {
      $orderDTO = OrderDTO::fromRequest($request->validated());
      $order = $this->orderService->createOrder($orderDTO);

      // Nếu phương thức thanh toán là VNPay, tạo URL thanh toán và trả về
      if ($orderDTO->method === 'vnpay') {
        $paymentUrl = $this->vnpayService->createPaymentUrl($order, $order->payment);

        return ResponseUtils::created([
          'order' => new OrderResource($order),
          'payment_url' => $paymentUrl,
          'redirect_required' => true,
          'client_success_url' => config('vnpay.clientSuccessUrl'),
          'client_failed_url' => config('vnpay.clientFailedUrl'),
        ], 'Đơn hàng đã được tạo. Vui lòng thanh toán để hoàn tất.');
      }

      return ResponseUtils::created([
        'order' => new OrderResource($order),
      ], ResponseMessage::CREATED_ORDER->value);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName,
        [
          'order' => $request->validated(),
        ],
      );
    }
  }

  /**
   * Cancel an order of the authenticated customer.
   *
   * @param Order $order
   * @return JsonResponse
   * @group Customer.Order
   * @authenticated
   */
  public function cancel(Order $order)
  {
    $user = AuthUtils::user();

    // Kiểm tra đơn hàng có thuộc về người dùng hiện tại không
    if ($order->customer_id != $user->id) {
      return ResponseUtils::forbidden('Bạn không có quyền hủy đơn hàng này.');
    }

    // Chỉ cho phép hủy đơn hàng khi đơn hàng đang ở trạng thái chờ xác nhận
    if (! in_array($order->status, [OrderStatus::PENDING->value])) {
      return ResponseUtils::badRequest('Không thể hủy đơn hàng ở trạng thái hiện tại.');
    }

    try {
      $cancelledOrder = $this->orderService->cancelOrder($order->id);

      return ResponseUtils::success([
        'order' => new OrderResource($cancelledOrder),
      ], 'Đơn hàng đã được hủy thành công.');
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName,
        [
          'customer_id' => $user->id,
          'order' => $order->id,
          'status' => $order->status,
        ],
      );
    }
  }

  /**
   * Update the status of an order of the authenticated customer to COMPLETED.
   *
   * @param Order $order
   * @return JsonResponse
   * @group Customer.Order
   * @authenticated
   */
  public function complete(Order $order)
  {
    $user = AuthUtils::user();

    // Kiểm tra đơn hàng có thuộc về người dùng hiện tại không
    if ($order->customer_id != $user->id) {
      return ResponseUtils::forbidden('Bạn không có quyền hoàn tất đơn hàng này.');
    }

    // Chỉ cho phép hoàn tất đơn hàng khi đơn hàng đang ở trạng thái DELIVERED
    if ($order->status != OrderStatus::DELIVERED->value) {
      return ResponseUtils::badRequest('Không thể hoàn tất đơn hàng ở trạng thái hiện tại.');
    }

    try {
      $completedOrder = $this->orderService->completeOrder($order->id);

      return ResponseUtils::success([
        'order' => new OrderResource($completedOrder),
      ], 'Đơn hàng đã được hoàn tất thành công.');
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'customer_id' => $user->id,
        'order' => $order->id,
        'status' => $order->status,
      ]);
    }
  }
}
