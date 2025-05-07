<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class ProcessOrderItemsAction extends BaseAction
{
  /**
   * Xử lý các mục trong đơn hàng
   *
   * @param Order $order
   * @param Collection $cartItems
   * @return float
   */
  public function execute(...$args): float
  {
    [$order, $cartItems] = $args;
    $totalAmount = 0.0;

    // Tạo các order item, xóa các cart item tương ứng
    foreach ($cartItems as $item) {
      $book_id = $item->book_id;
      $quantity = $item->quantity;
      $book = Book::find($book_id);
      $book_price = $book->price;

      // Tính discount value cho sách
      $discountedPrice = $book->getDiscountedPrice();
      $discount_value = $book_price - $discountedPrice;

      // Cộng dồn vào tổng số tiền
      $totalAmount += $discountedPrice * $quantity;

      // Xóa cart item
      $item->delete();

      $order->items()->create([
        'book_id' => $book_id,
        'quantity' => $quantity,
        'price_at_time' => $book_price,
        'discount_value' => $discount_value,
      ]);
    }

    return $totalAmount;
  }
}
