<?php

namespace App\Enums;

enum PaymentStatus: string
{
  case PENDING = 'pending';
  case PROCESSING = 'processing';
  case PAID = 'paid';
  case FAILED = 'failed';
  case REFUNDED = 'refunded';
  case CANCELED = 'canceled';

  public function label(): string
  {
    return match ($this) {
      self::PENDING => 'Đang chờ thanh toán',
      self::PROCESSING => 'Đang xử lý',
      self::PAID => 'Đã thanh toán',
      self::FAILED => 'Thanh toán thất bại',
      self::REFUNDED => 'Đã hoàn tiền',
      self::CANCELED => 'Đã hủy',
    };
  }
}
