<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\DTOs\DiscountDTO;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Support\Facades\DB;


class CreateDiscountAction extends BaseAction
{
    /**
     * Tạo mã giảm giá mới
     *
     * @param  DiscountDTO  $discountDTO
     * @param  array  $relations  Các mối quan hệ cần eager loading
     *
     * @throws Exception
     */
    public function execute(...$args): Discount
    {
        [$discountDTO, $relations] = $args;

        DB::beginTransaction();

        try {
            // Tạo mã giảm giá mới
            $discount = Discount::create([
                'name' => $discountDTO->name,
                'discount_type' => $discountDTO->discount_type,
                'discount_value' => $discountDTO->discount_value,
                'target_type' => $discountDTO->target_type ?? 'book',
                'min_purchase_amount' => $discountDTO->min_purchase_amount ?? 0,
                'max_discount_amount' => $discountDTO->max_discount_amount,
                'start_date' => $discountDTO->start_date,
                'end_date' => $discountDTO->end_date,
                'description' => $discountDTO->description,
                'is_active' => $discountDTO->is_active ?? true,
            ]);

            // Thêm targets nếu có và nếu là discount theo sách
            if ($discount->target_type === 'book' && ! empty($discountDTO->target_ids)) {
                $this->syncTargets($discount, $discountDTO->target_ids);
            }

            DB::commit();

            // Lấy discount với các mối quan hệ
            return ! empty($relations) ? $discount->fresh($relations) : $discount->fresh(['targets']);
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
