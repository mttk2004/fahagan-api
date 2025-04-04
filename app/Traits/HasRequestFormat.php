<?php

namespace App\Traits;

trait HasRequestFormat
{
    /**
     * Chuyển đổi request từ direct format sang JSON:API format
     *
     * @param array $keys Các thuộc tính cần chuyển đổi
     * @param bool $hasRelationships Request có relationships không
     * @return void
     */
    protected function convertToJsonApiFormat(array $keys = [], bool $hasRelationships = false): void
    {
        // Nếu đã có cấu trúc JSON:API, không cần chuyển đổi
        if ($this->has('data')) {
            return;
        }

        // Nếu có relationships, kiểm tra có được chuyển đổi hay không
        if ($hasRelationships) {
            // Đối với request có relationships, chúng ta ưu tiên format JSON:API
            // và không tự động chuyển đổi từ direct format
            return;
        }

        // Nếu có keys cụ thể và tất cả keys đều tồn tại trong request
        // thì mới thực hiện chuyển đổi
        if (!empty($keys) && !$this->hasAllKeys($keys)) {
            return;
        }

        // Lấy tất cả dữ liệu từ request
        $allData = $this->all();
        $attributes = [];
        $relationships = [];

        // Xác định attributes và relationships
        foreach ($allData as $key => $value) {
            // Nếu giá trị là một mảng và có cấu trúc relationships
            if (is_array($value) && isset($value['data'])) {
                $relationships[$key] = $value;
            } else {
                $attributes[$key] = $value;
            }
        }

        // Tạo cấu trúc JSON:API
        $jsonApiData = ['data' => ['attributes' => $attributes]];

        // Thêm relationships nếu có
        if (!empty($relationships)) {
            $jsonApiData['data']['relationships'] = $relationships;
        }

        // Thay thế dữ liệu request
        $this->replace($jsonApiData);
    }

    /**
     * Kiểm tra xem tất cả các khóa có tồn tại trong request không
     *
     * @param array $keys Các khóa cần kiểm tra
     * @return bool
     */
    protected function hasAllKeys(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }

        return true;
    }
}
