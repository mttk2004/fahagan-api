<?php

namespace App\Services;

use App\Actions\Orders\CreateOrderAction;
use App\Constants\ApplicationConstants;
use App\DTOs\OrderDTO;
use App\Enums\OrderStatus;
use App\Filters\OrderFilter;
use App\Http\Sorts\V1\OrderSort;
use App\Models\Order;
use App\Utils\AuthUtils;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class OrderService extends BaseService
{
    /**
     * OrderService constructor.
     */
    public function __construct(
        private readonly CartItemService $cartItemService,
        private readonly CreateOrderAction $createOrderAction,
        protected Model $model = new Order,
        protected string $filterClass = OrderFilter::class,
        protected string $sortClass = OrderSort::class,
        protected array $with = ['customer', 'employee', 'payment']
    ) {
    }

    /**
     * Get all orders with pagination and filtering.
     */
    public function getAllOrders(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Get order by ID.
     */
    public function getOrderById(int $id): Model
    {
        return $this->getById($id);
    }

    /**
     * Create a new order.
     *
     * @throws Exception
     */
    public function createOrder(OrderDTO $orderDTO): Order
    {
        return $this->createOrderAction->execute($orderDTO, $this->with);
    }

    /**
     * Get orders of a customer with pagination and filtering.
     */
    public function getCustomerOrders(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $customer = AuthUtils::user();

        // Thêm điều kiện lọc theo customer_id
        $query = $this->model->query()->where('customer_id', $customer->id);

        // Áp dụng bộ lọc và sắp xếp
        if ($this->filterClass && $request->has('filter')) {
            $query = app($this->filterClass)->filter($query, $request);
        }

        if ($this->sortClass && $request->has('sort')) {
            $query = app($this->sortClass)->sort($query, $request);
        }

        // Thêm các mối quan hệ
        if (! empty($this->with)) {
            $query->with($this->with);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get order details including order items.
     */
    public function getOrderDetails(int $order_id): Order
    {
        return $this->model->with([])->findOrFail($order_id);
    }

    /**
     * Cancel an order (for customer only).
     *
     * @throws Exception
     */
    public function cancelOrder(int $order_id): Order
    {
        $order = $this->getOrderById($order_id);

        // Kiểm tra trạng thái đơn hàng
        if (! in_array($order->status, [OrderStatus::PENDING->value])) {
            throw new Exception('Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        $order->status = OrderStatus::CANCELED->value;
        $order->canceled_at = now();
        $order->save();

        return $this->getOrderDetails($order->id);
    }

    /**
     * Complete an order (for customer only).
     *
     * @throws Exception
     */
    public function completeOrder(int $order_id): Order
    {
        $order = $this->getOrderById($order_id);

        // Kiểm tra trạng thái đơn hàng
        if ($order->status != OrderStatus::DELIVERED->value) {
            throw new Exception('Không thể hoàn tất đơn hàng ở trạng thái hiện tại.');
        }

        $order->status = OrderStatus::COMPLETED->value;
        $order->completed_at = now();
        $order->save();

        return $this->getOrderDetails($order->id);
    }

    /**
     * Update the status of an order (for employee only).
     *
     * @throws Exception
     */
    public function updateOrderStatus(int $order_id, string $status): Order
    {
        $order = $this->getOrderById($order_id);

        $currentStatus = OrderStatus::from($order->status);
        $newStatus = OrderStatus::from($status);

        // Kiểm tra xem có thể chuyển từ trạng thái hiện tại sang trạng thái mới không
        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw new Exception('Không thể cập nhật từ trạng thái '.$currentStatus->description().' sang '.$newStatus->description());
        }

        // Nếu chuyển từ PENDING sang APPROVED hoặc CANCELED, thêm nhân viên xử lý
        if ($currentStatus === OrderStatus::PENDING) {
            $order->employee_id = AuthUtils::user()->id;
        }

        // Nếu chuyển sang APPROVED, cập nhật approved_at
        // và cập nhật số lượng sách trong kho
        if ($newStatus === OrderStatus::APPROVED) {
            $order->approved_at = now();

            foreach ($order->items as $item) {
                $item->book->decrement('available_count', $item->quantity);
                $item->book->increment('sold_count', $item->quantity);
            }
        }

        // Nếu chuyển sang DELIVERED, cập nhật delivered_at
        if ($newStatus === OrderStatus::DELIVERED) {
            $order->delivered_at = now();
        }

        // Nếu chuyển sang COMPLETED, cập nhật completed_at
        if ($newStatus === OrderStatus::COMPLETED) {
            $order->completed_at = now();
        }

        // Nếu chuyển sang CANCELED, cập nhật canceled_at
        if ($newStatus === OrderStatus::CANCELED) {
            $order->canceled_at = now();
        }

        $order->status = $status;
        $order->save();

        return $this->getOrderDetails($order->id);
    }
}
