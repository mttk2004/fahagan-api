<?php

namespace App\Traits;

trait HasUpdateRules
{
    /**
     * Biến đổi rules thành update rules bằng cách bỏ 'required' và thêm 'sometimes'
     */
    public static function transformToUpdateRules(array $rules): array
    {
        return array_merge(
            ['sometimes'],
            array_filter($rules, fn ($rule) => $rule !== 'required')
        );
    }
}
