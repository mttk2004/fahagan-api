<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    /**
     * Lấy mô tả của trạng thái
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ duyệt',
            self::APPROVED => 'Đã duyệt đơn và chuẩn bị giao hàng',
            self::DELIVERING => 'Đang giao hàng',
            self::DELIVERED => 'Đã giao hàng',
            self::COMPLETED => 'Đã hoàn thành',
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
            self::APPROVED => $status == self::DELIVERING,
            self::DELIVERING => $status == self::DELIVERED,
            self::DELIVERED => $status == self::COMPLETED,
            self::COMPLETED, self::CANCELED => false,
        };
    }
}
