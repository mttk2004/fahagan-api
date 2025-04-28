<?php

namespace App\Traits\JsonApi;

use App\Utils\ResponseUtils;
use Illuminate\Http\JsonResponse;

trait HandleJsonApi
{
    /**
     * Kiểm tra xem request có tuân thủ định dạng JSON:API không
     *
     * @param array $validatedData Dữ liệu đã được validate từ request
     * @return JsonResponse|null Response lỗi nếu định dạng không đúng, null nếu hợp lệ
     */
    protected function validateJsonApiFormat(array $validatedData): ?JsonResponse
    {
        if (!isset($validatedData['data']) || !isset($validatedData['data']['attributes'])) {
            return ResponseUtils::badRequest('Yêu cầu phải theo định dạng JSON:API');
        }

        return null;
    }

    /**
     * Trích xuất IDs từ relationships trong định dạng JSON:API
     *
     * @param array $validatedData Dữ liệu đã được validate từ request
     * @param string $relationshipName Tên của relationship cần trích xuất (ví dụ: 'books')
     * @param bool $allowEmpty Cho phép trả về mảng rỗng nếu không tìm thấy relationship
     * @return array|null Mảng IDs hoặc null nếu không có relationship và $allowEmpty = false
     */
    protected function extractRelationshipIds(array $validatedData, string $relationshipName, bool $allowEmpty = true): ?array
    {
        if (!isset($validatedData['data']['relationships'][$relationshipName]['data'])) {
            return $allowEmpty ? [] : null;
        }

        return collect($validatedData['data']['relationships'][$relationshipName]['data'])
            ->pluck('id')
            ->toArray();
    }
}
