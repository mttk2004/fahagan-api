<?php

namespace App\Enums;

enum OrderStatus: string
{
  case PENDING = 'pending';
  case APPROVED = 'approved';
  case DELIVERED = 'delivered';
  case CANCELED = 'canceled';

  /**
   * Lấy mô tả của trạng thái
   */
  public function description(): string
  {
    return match ($this) {
      self::PENDING => 'Chờ duyệt',
      self::APPROVED => 'Đã duyệt đơn và tiến hành giao hàng',
      self::DELIVERED => 'Đã giao hàng',
      self::CANCELED => 'Đã hủy',
    };
  }

  /**
   * Kiểm tra trạng thái có thể chuyển từ trạng thái hiện tại không
   */
  public function canTransitionTo(OrderStatus $status): bool
  {
    return match ($this) {
      self::PENDING => in_array($status, [self::APPROVED, self::CANCELED]),
      self::APPROVED => in_array($status, [self::DELIVERED]),
      self::DELIVERED => false,
      self::CANCELED => false,
    };
  }
}
