<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\Models\Discount;
use Exception;
use Illuminate\Support\Facades\DB;

class DeleteDiscountAction extends BaseAction
{
    /**
     * Xóa mã giảm giá (soft delete)
     *
     * @param  Discount  $discount  Mã giảm giá cần xóa
     * @return Discount Mã giảm giá đã xóa
     *
     * @throws Exception
     */
    public function execute(...$args): Discount
    {
        [$discount] = $args;

        DB::beginTransaction();

        try {
            // Soft delete discount
            $discount->delete();

            DB::commit();

            return $discount;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
