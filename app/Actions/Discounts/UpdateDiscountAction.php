<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateDiscountAction extends BaseAction
{
  /**
   * Cập nhật mã giảm giá với dữ liệu và mối quan hệ mới
   *
   * @param Discount $discount Mã giảm giá cần cập nhật
   * @param array $data Dữ liệu cập nhật
   * @param array $relations Mối quan hệ cần cập nhật
   * @return Discount
   * @throws Exception
   */
  public function execute(...$args): Discount
  {
    [$discount, $data, $relations] = $args;

    DB::beginTransaction();

    try {
      // Cập nhật thông tin discount
      if (!empty($data)) {
        $discount->update($data);
      }

      // Cập nhật targets nếu có
      if (isset($relations['targets'])) {
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
   *
   * @param Discount $discount
   * @param array $targetIds
   * @return void
   */
  private function syncTargets(Discount $discount, array $targetIds): void
  {
    // Xóa targets hiện tại
    DiscountTarget::where('discount_id', $discount->id)->delete();

    // Thêm targets mới
    foreach ($targetIds as $targetId) {
      DiscountTarget::create([
        'discount_id' => $discount->id,
        'target_id' => $targetId,
        'target_type' => 'App\\Models\\Book', // Đường dẫn đầy đủ đến model Book
      ]);
    }
  }
}
