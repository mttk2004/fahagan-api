<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateOrderAction extends BaseAction
{
    /**
     * Tạo order mới
     *
     * @param mixed ...$args
     * @return Order
     * @throws Throwable
     */
    public function execute(...$args): Order
    {
        [$orderDTO, $relations] = $args;

        DB::beginTransaction();

        try {
            // Xác thực đơn hàng
            $validateOrderAction = new ValidateOrderAction();
            [$customer, $items, $address] = $validateOrderAction->execute($orderDTO);

            // Tạo order mới
            $order = Order::create([
              'customer_id' => $customer->id,
              'shopping_name' => $address->name,
              'shopping_phone' => $address->phone,
              'shopping_city' => $address->city,
              'shopping_district' => $address->district,
              'shopping_ward' => $address->ward,
              'shopping_address_line' => $address->address_line,
            ]);

            // Xử lý các mục trong đơn hàng
            $processOrderItemsAction = new ProcessOrderItemsAction();
            $totalAmount = $processOrderItemsAction->execute($order, $items);

            // Tạo thanh toán cho đơn hàng
            $createOrderPaymentAction = new CreateOrderPaymentAction();
            $createOrderPaymentAction->execute($order, $orderDTO, $totalAmount);

            DB::commit();

            // Lấy order với các mối quan hệ
            return ! empty($relations) ? $order->fresh($relations) : $order->fresh(['customer', 'employee']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
