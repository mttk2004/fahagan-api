<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateDiscountAction extends BaseAction
{
    /**
     * Cập nhật mã giảm giá với dữ liệu và mối quan hệ mới
     *
     * @param mixed ...$args
     *
     * @return Discount
     * @throws Throwable
     */
    public function execute(...$args): Discount
    {
        [$discount, $data, $relations] = $args;

        DB::beginTransaction();

        try {
            // Cập nhật thông tin discount
            if (! empty($data)) {
                $discount->update($data);
            }

            // Cập nhật targets chỉ khi là giảm giá theo sách
            if ($discount->target_type === 'book' && isset($relations['targets'])) {
                $this->syncTargets($discount, $relations['targets']);
            }

            DB::commit();

            // Lấy discount với các mối quan hệ đã được cập nhật
            return $discount->fresh(['targets']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Đồng bộ targets với mã giảm giá
     */
    private function syncTargets(Discount $discount, array $targetIds): void
    {
        // Xóa targets hiện tại
        DiscountTarget::where('discount_id', $discount->id)->delete();

        // Thêm targets mới
        $records = [];
        foreach ($targetIds as $targetId) {
            $records[] = [
                'discount_id' => $discount->id,
                'target_id' => $targetId,
            ];
        }

        if (! empty($records)) {
            DiscountTarget::insert($records);
        }
    }
}
