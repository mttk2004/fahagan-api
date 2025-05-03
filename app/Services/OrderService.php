<?php

namespace App\Services;

use App\Actions\Orders\CreateOrderAction;
use App\Constants\ApplicationConstants;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Filters\OrderFilter;
use App\Http\Sorts\V1\OrderSort;
use App\Models\Order;
use App\Models\User;
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
        protected Model $model = new Order(),
        protected string $filterClass = OrderFilter::class,
        protected string $sortClass = OrderSort::class,
        protected array $with = ['customer']
    ) {
    }

    /**
     * Get all orders with pagination and filtering.
     *
     * @param Request $request
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllOrders(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Get order by ID.
     *
     * @param int $id
     * @return Model
     */
    public function getOrderById(int $id): Model
    {
        return $this->getById($id);
    }

    /**
     * Create a new order.
     *
     * @param OrderDTO $orderDTO
     * @return Order
     * @throws Exception
     */
    public function createOrder(OrderDTO $orderDTO): Order
    {
        return $this->createOrderAction->execute($orderDTO, $this->with);
    }

    /**
     * Get orders of a customer with pagination and filtering.
     *
     * @param User $user
     * @param Request $request
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCustomerOrders(User $user, Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $query = $this->model->where('user_id', $user->id);

        // Apply filters
        if (isset($this->filterClass)) {
            $filter = new $this->filterClass($request);
            $query = $filter->apply($query);
        }

        // Apply sorts
        if (isset($this->sortClass) && method_exists($this->sortClass, 'apply')) {
            $sort = new $this->sortClass($request);
            $query = $sort->apply($query);
        } else {
            $query = $query->orderBy('created_at', 'desc');
        }

        // Apply relationships
        if (isset($this->with)) {
            $query = $query->with($this->with);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get order details including order items.
     *
     * @param int $orderId
     * @return Order
     */
    public function getOrderDetails(int $orderId): Order
    {
        return $this->model->with(['items.book', 'address'])->findOrFail($orderId);
    }

    /**
     * Cancel an order.
     *
     * @param int $orderId
     * @return Order
     * @throws Exception
     */
    public function cancelOrder(int $orderId): Order
    {
        $order = $this->getOrderById($orderId);

        // Kiểm tra trạng thái đơn hàng
        if (! in_array($order->status, [OrderStatus::PENDING->value, OrderStatus::PAID->value])) {
            throw new Exception('Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        $order->status = OrderStatus::CANCELED->value;
        $order->save();

        return $this->getOrderDetails($order->id);
    }
}
