<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\Order;
use App\Utils\AuthUtils;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderAction extends BaseAction
{
    /**
     * Tạo order mới
     *
     * @param OrderDTO $orderDTO
     * @param array $relations Các mối quan hệ cần eager loading
     * @return Order
     * @throws Exception
     */
    public function execute(...$args): Order
    {
        [$orderDTO, $relations] = $args;

        DB::beginTransaction();

        try {
            // Kiểm tra số lượng trong kho
            foreach ($orderDTO->items as $item) {
                $cartItem = CartItem::find($item['id']);
                $book = Book::find($cartItem->book_id);
                if ($book->available_count < $cartItem->quantity) {
                    throw new Exception('Số lượng trong kho không đủ cho sách ' . $book->title);
                }
            }

            // Kiểm tra địa chỉ giao hàng
            $address = Address::where('id', $orderDTO->address_id)
              ->where('user_id', AuthUtils::user()->id)
              ->first();
            if (! $address) {
                throw new Exception('Địa chỉ giao hàng không tồn tại.');
            }

            // Tạo order mới
            $order = Order::create([
              'customer_id' => AuthUtils::user()->id,
              'shopping_name' => $address->name,
              'shopping_phone' => $address->phone,
              'shopping_city' => $address->city,
              'shopping_district' => $address->district,
              'shopping_ward' => $address->ward,
              'shopping_address_line' => $address->address_line,
            ]);

            $totalAmount = 0.0;
            // Tạo các order item, xóa các cart item tương ứng
            // Chưa cần cập nhật số lượng sách
            // Nó sẽ được cập nhật khi nhân viên cập nhật đơn hàng sang trạng thái APPROVED
            foreach ($orderDTO->items as $item) {
                $cartItem = CartItem::find($item['id']);
                $book_id = $cartItem->book_id;
                $quantity = $cartItem->quantity;
                $book = Book::find($book_id);
                $book_price = $book->price;

                // Tính discount value cho sách
                $discountedPrice = $book->getDiscountedPrice();
                $discount_value = $book_price - $discountedPrice;

                // Cộng dồn vào tổng số tiền
                $totalAmount += $discountedPrice * $quantity;

                // Xóa cart item
                $cartItem->delete();

                $order->items()->create([
                  'book_id' => $book_id,
                  'quantity' => $quantity,
                  'price_at_time' => $book_price,
                  'discount_value' => $discount_value,
                ]);
            }

            // Tìm giảm giá tốt nhất cho đơn hàng
            $bestOrderDiscount = $order->getBestDiscount();
            $orderDiscountValue = 0;

            if ($bestOrderDiscount) {
                $orderDiscountValue = $order->getDiscountedValue();
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
            $order->payment()->create([
              'method' => $orderDTO->method,
              'total_amount' => $finalAmount,
              'discount_value' => $orderDiscountValue,
              'status' => $paymentStatus,
            ]);

            DB::commit();

            // Lấy order với các mối quan hệ
            return ! empty($relations) ? $order->fresh($relations) : $order->fresh(['customer', 'employee']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
