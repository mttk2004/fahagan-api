<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationRuleParser;

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
     * Lấy thông báo lỗi chuẩn cho JSON API format
     *
     * @return array
     */
    public static function getJsonApiMessages(): array
    {
        $messages = [];

        // Lấy tất cả các trường hợp từ enum ValidationCase
        if (method_exists(static::class, 'getValidationCases')) {
            $cases = static::getValidationCases();

            foreach ($cases as $case) {
                $fieldName = self::getCaseField($case);
                $ruleName = self::getCaseRule($case);

                // Không thêm tiền tố data.attributes.
                $qualifiedFieldName = $fieldName;

                // Thêm thông báo lỗi cho trường hợp này
                if (method_exists(static::class, 'getValidationMessage')) {
                    $messages[$qualifiedFieldName . '.' . $ruleName] = static::getValidationMessage($case);
                }
            }
        }

        return $messages;
    }

    /**
     * Lấy các thông báo lỗi tiêu chuẩn từ cases
     *
     * @return array
     */
    public static function getStandardMessages(): array
    {
        if (!method_exists(static::class, 'getValidationCases') || !method_exists(static::class, 'getValidationMessage')) {
            return [];
        }

        $messages = [];
        $cases = static::getValidationCases();

        foreach ($cases as $case) {
            $fieldName = self::getCaseField($case);
            $ruleName = self::getCaseRule($case);
            $messages[$fieldName . '.' . $ruleName] = static::getValidationMessage($case);
        }

        return $messages;
    }

    /**
     * Xử lý phân tích tên field từ case
     *
     * @param mixed $case
     * @return string
     */
    protected static function getCaseField($case): string
    {
        $caseParts = explode('_', $case->name);
        array_pop($caseParts); // Loại bỏ phần cuối (tên rule)
        return Str::snake(implode('_', $caseParts));
    }

    /**
     * Xử lý phân tích tên rule từ case
     *
     * @param mixed $case
     * @return string
     */
    protected static function getCaseRule($case): string
    {
        $caseParts = explode('_', $case->name);
        $ruleName = end($caseParts);
        return ValidationRuleParser::parse(Str::snake($ruleName))[0];
    }

    /**
     * Phương thức tạo thông báo lỗi cho relationships trong JSON API
     *
     * @param array $relationships Mảng các relationships cần tạo thông báo lỗi
     * @return array Mảng thông báo lỗi cho relationships
     *
     * Ví dụ cấu trúc $relationships:
     * [
     *    'authors' => ['required', 'integer', 'exists'], // mỗi item có thể là mảng rule hoặc string
     *    'genres' => ['required', 'integer', 'exists'],  // rule ID
     * ]
     */
    public static function getRelationshipMessages(array $relationships): array
    {
        $messages = [];

        foreach ($relationships as $relation => $rules) {
            // Kiểm tra xem $rules có phải là mảng không
            if (!is_array($rules)) {
                $rules = [$rules];
            }

            // Xác định xem relation là dạng has-many hay has-one
            $isHasMany = str_ends_with($relation, 's'); // Giả định chuẩn đặt tên số nhiều

            // Tạo thông báo cho mỗi rule
            foreach ($rules as $rule) {
                if (empty($rule)) continue;

                $message = null;

                // Tìm enum case tương ứng và lấy message
                foreach (self::cases() as $case) {
                    $parts = explode('_', $case->name);
                    if (count($parts) < 2) {
                        continue;
                    }

                    $field = strtolower(implode('_', array_slice($parts, 0, -1)));
                    $ruleInCase = strtolower(end($parts));

                    // So sánh field_id và rule
                    $expectedField = strtoupper(preg_replace('/s$/', '', $relation)) . '_ID';
                    if ($field === $expectedField && $ruleInCase === $rule) {
                        $message = $case->message();
                        break;
                    }
                }

                // Nếu không tìm thấy message cụ thể, dùng message mặc định
                if (!$message) {
                    if ($rule === 'exists') {
                        $message = "Không tìm thấy " . strtolower(preg_replace('/s$/', '', $relation)) . " này trong hệ thống.";
                    } else if ($rule === 'required') {
                        $message = "Trường này không được để trống.";
                    } else if ($rule === 'integer') {
                        $message = "Trường này phải là số nguyên.";
                    } else {
                        $message = "Trường này không hợp lệ.";
                    }
                }

                // Tạo patterns cho các trường hợp cụ thể
                if ($isHasMany) {
                    // Has-many relationships (authors, genres)

                    // Trường hợp cụ thể: data.relationships.genres.data.0.id, data.relationships.genres.data.1.id,...
                    for ($i = 0; $i < 10; $i++) {  // Giả sử tối đa 10 relation items
                        $key = "data.relationships.$relation.data.$i.id";
                        $messages[$key] = $message;
                    }

                    // Các pattern tổng quát sử dụng ký tự đại diện (wildcard)
                    $patterns = [
                        "data.relationships.$relation.data.*.id.$rule",  // Pattern chuẩn
                        "data.relationships.$relation.data.*.$rule",     // Pattern phụ
                        "data.relationships.$relation.data.*.id",        // Pattern không rule
                    ];
                } else {
                    // Has-one relationships (publisher)
                    $patterns = [
                        "data.relationships.$relation.data.id.$rule",    // Pattern chuẩn
                        "data.relationships.$relation.id.$rule",         // Pattern phụ
                        "data.relationships.$relation.data.id",          // Pattern không rule
                        "data.relationships.$relation.id",               // Pattern không rule phụ
                    ];
                }

                // Áp dụng message cho tất cả các patterns
                foreach ($patterns as $pattern) {
                    $messages[$pattern] = $message;
                }
            }
        }

        return $messages;
    }
}
