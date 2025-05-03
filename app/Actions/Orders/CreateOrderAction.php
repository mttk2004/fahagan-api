<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
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
      // Tạo order mới
      $order = Order::create([
        'customer_id' => AuthUtils::user()->id,
        'shopping_name' => $orderDTO->shopping_name,
        'shopping_phone' => $orderDTO->shopping_phone,
        'shopping_city' => $orderDTO->shopping_city,
        'shopping_district' => $orderDTO->shopping_district,
        'shopping_ward' => $orderDTO->shopping_ward,
        'shopping_address_line' => $orderDTO->shopping_address_line,
      ]);

      // Tạo các order item và xóa các cart item tương ứng
      foreach ($orderDTO->items as $item) {
        $cartItem = CartItem::find($item['id']);
        $book_id = $cartItem->book_id;
        $cartItem->delete();

        $order->items()->create([
          'book_id' => $book_id,
          'quantity' => $item['quantity'],
          'price_at_time' => Book::find($book_id)->price,
        ]);
      }

      // TODO: Tạo payment cho order

      DB::commit();

      // Lấy order với các mối quan hệ
      return ! empty($relations) ? $order->fresh($relations) : $order->fresh(['customer', 'employee']);
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }
}
