<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPING = 'shipping';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    /**
     * Lấy mô tả của trạng thái
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ xác nhận',
            self::CONFIRMED => 'Đã xác nhận',
            self::PROCESSING => 'Đang xử lý',
            self::SHIPPING => 'Đang giao hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::CANCELLED => 'Đã hủy',
            self::RETURNED => 'Đã trả hàng',
        };
    }

    /**
     * Kiểm tra trạng thái có thể chuyển từ trạng thái hiện tại không
     */
    public function canTransitionTo(OrderStatus $status): bool
    {
        return match ($this) {
            self::PENDING => in_array($status, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($status, [self::PROCESSING, self::CANCELLED]),
            self::PROCESSING => in_array($status, [self::SHIPPING, self::CANCELLED]),
            self::SHIPPING => in_array($status, [self::DELIVERED, self::RETURNED]),
            self::DELIVERED => in_array($status, [self::RETURNED]),
            self::CANCELLED => false,
            self::RETURNED => false,
        };
    }
}
