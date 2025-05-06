<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\Models\Discount;
use Illuminate\Validation\ValidationException;

class ValidateDiscountAction extends BaseAction
{
    /**
     * Xác thực dữ liệu mã giảm giá
     *
     * @param mixed ...$args
     * @return bool
     */
    public function execute(...$args): bool
    {
        if (count($args) === 1) {
            [$discountDTO] = $args;
            $forUpdate = false;
            $discount = null;
        } else {
            [$discountDTO, $forUpdate, $discount] = $args;
        }

        // Xác thực các trường bắt buộc
        if (! $forUpdate) {
            if (empty($discountDTO->name)) {
                throw ValidationException::withMessages([
                    'data.attributes.name' => ['Tên mã giảm giá là bắt buộc.'],
                ]);
            }

            if (! isset($discountDTO->discount_type)) {
                throw ValidationException::withMessages([
                    'data.attributes.discount_type' => ['Loại giảm giá là bắt buộc.'],
                ]);
            }

            if (! isset($discountDTO->discount_value)) {
                throw ValidationException::withMessages([
                    'data.attributes.discount_value' => ['Giá trị giảm giá là bắt buộc.'],
                ]);
            }
        }

        // Xác thực loại giảm giá
        if (isset($discountDTO->discount_type) && ! in_array($discountDTO->discount_type, ['percentage', 'fixed'])) {
            throw ValidationException::withMessages([
                'data.attributes.discount_type' => ['Loại giảm giá không hợp lệ. Các giá trị hợp lệ: percentage, fixed.'],
            ]);
        }

        // Xác thực loại đích giảm giá
        if (isset($discountDTO->target_type) && ! in_array($discountDTO->target_type, ['book', 'order'])) {
            throw ValidationException::withMessages([
                'data.attributes.target_type' => ['Loại đích giảm giá không hợp lệ. Các giá trị hợp lệ: book, order.'],
            ]);
        }

        // Xác thực giá trị giảm giá
        if (isset($discountDTO->discount_value)) {
            if ($discountDTO->discount_value < 0) {
                throw ValidationException::withMessages([
                    'data.attributes.discount_value' => ['Giá trị giảm giá không được âm.'],
                ]);
            }

            if (isset($discountDTO->discount_type) && $discountDTO->discount_type === 'percentage' && $discountDTO->discount_value > 100) {
                throw ValidationException::withMessages([
                    'data.attributes.discount_value' => ['Giá trị giảm giá theo phần trăm không được vượt quá 100%.'],
                ]);
            }
        }

        // Xác thực ngày bắt đầu và kết thúc
        if (isset($discountDTO->start_date) && isset($discountDTO->end_date) && $discountDTO->start_date > $discountDTO->end_date) {
            throw ValidationException::withMessages([
                'data.attributes.end_date' => ['Ngày kết thúc phải sau ngày bắt đầu.'],
            ]);
        }

        // Xác thực targets khi chọn target_type là book
        if (isset($discountDTO->target_type) && $discountDTO->target_type === 'book' && empty($discountDTO->target_ids)) {
            throw ValidationException::withMessages([
                'data.relationships.targets.data' => ['Vui lòng chọn ít nhất một sách để áp dụng mã giảm giá.'],
            ]);
        }

        // Xác thực trùng lặp nếu đang tạo mới hoặc thay đổi tên khi cập nhật
        if (! $forUpdate) {
            $this->validateDiscountNameDoesNotExist($discountDTO->name);
        } elseif ($discount && isset($discountDTO->name) && $discountDTO->name !== $discount->name) {
            $this->validateDiscountNameDoesNotExist($discountDTO->name);
        }

        return true;
    }

    /**
     * Xác thực tên mã giảm giá không tồn tại
     *
     * @throws ValidationException
     */
    private function validateDiscountNameDoesNotExist(string $name): void
    {
        $existingDiscount = Discount::where('name', $name)->first();

        if ($existingDiscount) {
            throw ValidationException::withMessages([
                'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
            ]);
        }
    }
}
