<?php

namespace App\Traits;

use Illuminate\Validation\Rule;

trait HasUniqueRules
{
    /**
     * Thêm rule unique vào mảng rules
     */
    public static function addUniqueRule(array $rules, string $table, string $column, ?string $id = null, ?string $idColumn = null, ?string $deleteColumn = 'deleted_at'): array
    {
        $uniqueRule = Rule::unique($table, $column);

        // Nếu có deleteColumn, thêm điều kiện whereNull
        if ($deleteColumn !== null) {
            $uniqueRule->whereNull($deleteColumn);
        }

        // Nếu có id, thêm điều kiện ignore
        if ($id !== null) {
            $uniqueRule->ignore($id, $idColumn);
        }

        return array_merge($rules, [$uniqueRule]);
    }

    /**
     * Phương thức tĩnh tạo rule unique cho validation
     */
    public static function createUniqueRule(string $table, string $column, ?string $id = null, ?string $idColumn = null, ?string $deleteColumn = 'deleted_at'): object
    {
        $uniqueRule = Rule::unique($table, $column);

        // Nếu có deleteColumn, thêm điều kiện whereNull
        if ($deleteColumn !== null) {
            $uniqueRule->whereNull($deleteColumn);
        }

        // Nếu có id, thêm điều kiện ignore
        if ($id !== null) {
            $uniqueRule->ignore($id, $idColumn);
        }

        return $uniqueRule;
    }
}
