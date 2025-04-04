<?php

namespace App\Traits;

trait HasStandardValidationMessages
{
    /**
     * Phương thức tiêu chuẩn để tạo thông báo cho validate
     */
    public static function getMessages(): array
    {
        $messages = [];
        foreach (self::cases() as $case) {
            // Phân tách tên case, phần đầu là field, phần sau là rule
            $parts = explode('_', $case->name);
            if (count($parts) < 2) {
                continue;
            }

            // Lấy tên field (có thể có nhiều phần)
            $field = strtolower(implode('_', array_slice($parts, 0, -1)));

            // Lấy rule name (phần cuối)
            $rule = strtolower(end($parts));

            // Map đến thông báo lỗi
            $messages["$field.$rule"] = $case->message();
        }

        return $messages;
    }

    /**
     * Phương thức tiêu chuẩn để tạo thông báo cho JSON API validate
     */
    public static function getJsonApiMessages(string $prefix = 'data.attributes'): array
    {
        $messages = [];
        foreach (self::cases() as $case) {
            // Phân tách tên case, phần đầu là field, phần sau là rule
            $parts = explode('_', $case->name);
            if (count($parts) < 2) {
                continue;
            }

            // Lấy tên field (có thể có nhiều phần)
            $field = strtolower(implode('_', array_slice($parts, 0, -1)));

            // Lấy rule name (phần cuối)
            $rule = strtolower(end($parts));

            // Map đến thông báo lỗi với prefix cho JSON API
            $messages["$prefix.$field.$rule"] = $case->message();
        }

        return $messages;
    }
}
