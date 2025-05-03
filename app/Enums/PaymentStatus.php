<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';

    /**
     * Lấy mô tả của trạng thái
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ thanh toán',
            self::PAID => 'Đã thanh toán',
            self::FAILED => 'Thanh toán thất bại',
        };
    }

    /**
     * Kiểm tra trạng thái có thể chuyển từ trạng thái hiện tại không
     */
    public function canTransitionTo(OrderStatus $status): bool
    {
        return match ($this) {
            self::PENDING => in_array($status, [self::PAID, self::FAILED]),
            self::PAID => false,
            self::FAILED => false,
        };
    }
}
