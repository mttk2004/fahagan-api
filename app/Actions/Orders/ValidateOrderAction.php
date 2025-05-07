<?php

namespace App\Actions\Orders;

use App\Actions\BaseAction;
use App\Models\Address;
use App\Models\Book;
use App\Utils\AuthUtils;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ValidateOrderAction extends BaseAction
{
  /**
   * Kiểm tra điều kiện tạo đơn hàng
   *
   * @param object $orderDTO
   * @return array
   * @throws Exception
   */
  public function execute(...$args): array
  {
    [$orderDTO] = $args;
    $customer = AuthUtils::user();
    $items = $customer->cartItems;

    // Kiểm tra giỏ hàng có trống không
    if ($items->isEmpty()) {
      throw new Exception('Giỏ hàng của bạn đang trống.');
    }

    // Kiểm tra số lượng trong kho
    foreach ($items as $item) {
      $book = Book::find($item->book_id);
      if ($book->available_count < $item->quantity) {
        throw new Exception('Số lượng trong kho không đủ cho sách ' . $book->title);
      }
    }

    // Kiểm tra địa chỉ giao hàng
    $address = Address::where('id', $orderDTO->address_id)
      ->where('user_id', $customer->id)
      ->first();
    if (! $address) {
      throw new Exception('Địa chỉ giao hàng không tồn tại hoặc không thuộc tài khoản của bạn.');
    }

    return [$customer, $items, $address];
  }
}
