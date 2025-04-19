<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\DTOs\Discount\DiscountDTO;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Support\Facades\DB;

class RestoreDiscountAction extends BaseAction
{
    /**
     * Khôi phục mã giảm giá đã bị xóa mềm và cập nhật thông tin mới
     *
     * @param Discount $trashedDiscount Mã giảm giá đã bị xóa mềm
     * @param DiscountDTO $discountDTO Dữ liệu để cập nhật
     * @return Discount
     * @throws Exception
     */
    public function execute(...$args): Discount
    {
        [$trashedDiscount, $discountDTO] = $args;

        DB::beginTransaction();

        try {
            // Khôi phục mã giảm giá
            $trashedDiscount->restore();

            // Cập nhật thông tin từ DTO
            $data = $discountDTO->toArray();
            $trashedDiscount->update($data);

            // Xử lý targets nếu có VÀ nếu discount là loại book
            if ($trashedDiscount->target_type === 'book' && ! empty($discountDTO->target_ids)) {
                // Xóa targets hiện tại
                DiscountTarget::where('discount_id', $trashedDiscount->id)->delete();

                // Thêm targets mới sử dụng batch insert để tối ưu hiệu suất
                $records = [];
                foreach ($discountDTO->target_ids as $targetId) {
                    $records[] = [
                      'discount_id' => $trashedDiscount->id,
                      'target_id' => $targetId,
                    ];
                }

                if (! empty($records)) {
                    DiscountTarget::insert($records);
                }
            }

            DB::commit();

            // Lấy mã giảm giá với các mối quan hệ
            return $trashedDiscount->fresh(['targets']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
