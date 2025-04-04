<?php

namespace App\Traits;

trait HasStandardValidationRules
{
    /**
     * Phương thức tiêu chuẩn để lấy rules cho create
     */
    public static function getCreationRules(): array
    {
        $rules = [];
        foreach (self::cases() as $case) {
            $field = strtolower($case->name);
            $rules[$field] = $case->rules();
        }

        return $rules;
    }

    /**
     * Phương thức tiêu chuẩn để lấy rules cho update
     */
    public static function getUpdateRules(): array
    {
        $rules = [];
        foreach (self::cases() as $case) {
            $field = strtolower($case->name);
            $rules[$field] = self::transformToUpdateRules($case->rules());
        }

        return $rules;
    }

    /**
     * Phương thức tiêu chuẩn để tạo prefixed rules cho JSON API
     */
    public static function getJsonApiRules(string $prefix = 'data.attributes'): array
    {
        $rules = [];
        foreach (self::cases() as $case) {
            $field = strtolower($case->name);
            $rules["$prefix.$field"] = $case->rules();
        }

        return $rules;
    }
}
