<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\DTOs\Discount\DiscountDTO;
use App\Models\Discount;

class FindTrashedDiscountAction extends BaseAction
{
    /**
     * Tìm mã giảm giá đã bị xóa mềm với tên cụ thể
     *
     * @param DiscountDTO $discountDTO
     * @return Discount|null
     */
    public function execute(...$args): ?Discount
    {
        [$discountDTO] = $args;

        if (! isset($discountDTO->name)) {
            return null;
        }

        return Discount::withTrashed()
          ->where('name', $discountDTO->name)
          ->onlyTrashed()
          ->first();
    }
}
