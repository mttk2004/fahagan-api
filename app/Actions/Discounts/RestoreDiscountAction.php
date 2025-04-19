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

      // Xử lý targets nếu có
      if (!empty($discountDTO->target_ids)) {
        // Xóa targets hiện tại
        DiscountTarget::where('discount_id', $trashedDiscount->id)->delete();

        // Thêm targets mới
        foreach ($discountDTO->target_ids as $targetId) {
          DiscountTarget::create([
            'discount_id' => $trashedDiscount->id,
            'target_id' => $targetId,
            'target_type' => 'App\\Models\\Book', // Đường dẫn đầy đủ đến model Book
          ]);
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
