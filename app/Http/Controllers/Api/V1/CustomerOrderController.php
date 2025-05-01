<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OrderStoreRequest;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

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
        $user = AuthUtils::user();
        $orders = $this->orderService->getCustomerOrders($user, $request);

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
        if ($order->user_id !== $user->id) {
            return ResponseUtils::forbidden('Bạn không có quyền truy cập đơn hàng này.');
        }

        $orderWithDetails = $this->orderService->getOrderDetails($order->id);

        return ResponseUtils::success([
          'order' => new OrderResource($orderWithDetails),
        ]);
    }

    /**
     * Create a new order from the cart of the authenticated customer.
     *
     * @param OrderStoreRequest $request
     * @return JsonResponse
     * @group Customer.Order
     * @authenticated
     */
    public function store(OrderStoreRequest $request)
    {
        $user = AuthUtils::user();

        try {
            $order = $this->orderService->createOrderFromCart($user, $request->validated());

            return ResponseUtils::created([
              'order' => new OrderResource($order),
            ], 'Đơn hàng đã được tạo thành công.');
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
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
        if ($order->user_id !== $user->id) {
            return ResponseUtils::forbidden('Bạn không có quyền hủy đơn hàng này.');
        }

        // Chỉ cho phép hủy đơn hàng khi đơn hàng đang ở trạng thái chờ xác nhận hoặc đã xác nhận
        if (! in_array($order->status, [OrderStatus::PENDING->value, OrderStatus::CONFIRMED->value])) {
            return ResponseUtils::badRequest('Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        try {
            $cancelledOrder = $this->orderService->cancelOrder($order->id);

            return ResponseUtils::success([
              'order' => new OrderResource($cancelledOrder),
            ], 'Đơn hàng đã được hủy thành công.');
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }
}
