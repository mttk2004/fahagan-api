<?php

namespace App\Utils;

use Illuminate\Http\Request;

/**
 * Lớp hỗ trợ xử lý định dạng JSON:API
 */
class JsonApiFormatter
{
    /**
     * Lấy attributes từ request theo định dạng JSON:API
     *
     * @param Request $request
     * @return array
     */
    public static function getAttributes(Request $request): array
    {
        $data = $request->input('data');

        if (! $data || ! isset($data['attributes'])) {
            return [];
        }

        return $data['attributes'];
    }

    /**
     * Lấy relationships từ request theo định dạng JSON:API
     *
     * @param Request $request
     * @return array
     */
    public static function getRelationships(Request $request): array
    {
        $data = $request->input('data');

        if (! $data || ! isset($data['relationships'])) {
            return [];
        }

        return $data['relationships'];
    }

    /**
     * Kiểm tra xem request có đúng định dạng JSON:API không
     *
     * @param Request $request
     * @return bool
     */
    public static function isValidJsonApiFormat(Request $request): bool
    {
        $data = $request->input('data');

        if (! $data || ! is_array($data)) {
            return false;
        }

        // Kiểm tra các thành phần bắt buộc
        if (! isset($data['type'])) {
            return false;
        }

        // Phải có ít nhất một trong hai: attributes hoặc relationships
        if (! isset($data['attributes']) && ! isset($data['relationships'])) {
            return false;
        }

        return true;
    }
}
