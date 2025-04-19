<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\DTOs\Discount\DiscountDTO;
use App\Models\Discount;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateDiscountAction extends BaseAction
{
    /**
     * Tạo mã giảm giá mới
     *
     * @param DiscountDTO $discountDTO
     * @param array $relations Các mối quan hệ cần eager loading
     * @return Discount
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
              'start_date' => $discountDTO->start_date,
              'end_date' => $discountDTO->end_date,
            ]);

            // Thêm targets nếu có
            if (! empty($discountDTO->target_ids)) {
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
     *
     * @param Discount $discount
     * @param array $targetIds
     * @return void
     */
    private function syncTargets(Discount $discount, array $targetIds): void
    {
        foreach ($targetIds as $targetId) {
            $discount->targets()->create([
              'target_id' => $targetId,
              'target_type' => 'App\\Models\\Book', // Đường dẫn đầy đủ đến model Book
            ]);
        }
    }
}
