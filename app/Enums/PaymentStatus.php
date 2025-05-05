<?php

namespace App\Enums;

enum PaymentStatus: string
{
  case PENDING = 'pending';
  case PAID = 'paid';
  case FAILED = 'failed';

  public function label(): string
  {
    return match ($this) {
      self::PENDING => 'Đang chờ thanh toán',
      self::PAID => 'Đã thanh toán',
      self::FAILED => 'Thanh toán thất bại',
    };
  }
}
