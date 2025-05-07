<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
use App\Enums\PaymentStatus;
use App\Models\Order;

class CreateOrderPaymentAction extends BaseAction
{
    /**
     * Tạo thanh toán cho đơn hàng
     *
     * @param Order $order
     * @param object $orderDTO
     * @param float $totalAmount
     * @return mixed
     */
    public function execute(...$args): mixed
    {
        [$order, $orderDTO, $totalAmount] = $args;

        // Tìm giảm giá tốt nhất cho đơn hàng
        $bestOrderDiscount = $order->getBestDiscount();
        $orderDiscountValue = 0;

        if ($bestOrderDiscount) {
            $orderDiscountValue = $totalAmount - $bestOrderDiscount->value;
        }

        // Tính tổng số tiền cuối cùng sau khi áp dụng giảm giá đơn hàng
        $finalAmount = max(0, $totalAmount - $orderDiscountValue);

        // Xác định trạng thái thanh toán dựa trên phương thức
        $paymentStatus = PaymentStatus::PENDING;

        // Nếu là thanh toán tiền mặt, đánh dấu là đã thanh toán ngay
        if ($orderDTO->method === 'cod') {
            $paymentStatus = PaymentStatus::PAID;
        }

        // Tạo payment cho order
        $payment = $order->payment()->create([
          'method' => $orderDTO->method,
          'total_amount' => $finalAmount,
          'discount_value' => $orderDiscountValue,
          'status' => $paymentStatus,
        ]);

        return $payment;
    }
}
