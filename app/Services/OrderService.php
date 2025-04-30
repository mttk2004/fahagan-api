<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\Enums\OrderStatus;
use App\Filters\OrderFilter;
use App\Http\Sorts\V1\OrderSort;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService extends BaseService
{
    /**
     * OrderService constructor.
     */
    public function __construct(
        private readonly CartItemService $cartItemService
    ) {
        $this->model = new Order();
        $this->filterClass = OrderFilter::class;
        $this->sortClass = OrderSort::class;
        $this->with = ['customer'];
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
     * Create order from cart.
     *
     * @param User $user
     * @param array $orderData
     * @return Order
     * @throws Exception
     */
    public function createOrderFromCart(User $user, array $orderData): Order
    {
        // Kiểm tra giỏ hàng có trống không
        $cartItems = $this->cartItemService->getCartItems($user);
        if ($cartItems->isEmpty()) {
            throw new Exception('Giỏ hàng của bạn đang trống.');
        }

        // Bắt đầu transaction
        DB::beginTransaction();

        try {
            // Tạo đơn hàng
            $order = new Order();
            $order->user_id = $user->id;
            $order->address_id = $orderData['shipping_address_id'];
            $order->status = OrderStatus::PENDING->value;
            $order->payment_method = $orderData['payment_method'];
            $order->note = $orderData['note'] ?? null;

            // TODO: Xử lý mã giảm giá nếu có
            $order->coupon_code = $orderData['coupon_code'] ?? null;

            // Tính tổng tiền
            $total = 0;
            foreach ($cartItems as $cartItem) {
                $total += $cartItem->book->price * $cartItem->quantity;
            }

            $order->subtotal = $total;
            $order->shipping_fee = 0; // TODO: Tính phí vận chuyển
            $order->discount = 0; // TODO: Tính giảm giá
            $order->total = $total + $order->shipping_fee - $order->discount;

            $order->save();

            // Tạo các order item
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                  'book_id' => $cartItem->book_id,
                  'quantity' => $cartItem->quantity,
                  'price' => $cartItem->book->price,
                ]);
            }

            // Xóa giỏ hàng
            $this->cartItemService->clearCart($user);

            DB::commit();

            return $this->getOrderDetails($order->id);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
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
        if (! in_array($order->status, [OrderStatus::PENDING->value, OrderStatus::CONFIRMED->value])) {
            throw new Exception('Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        $order->status = OrderStatus::CANCELLED->value;
        $order->save();

        return $this->getOrderDetails($order->id);
    }
}
