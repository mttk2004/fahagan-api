<?php

namespace App\Actions\Discounts;

use App\Actions\BaseAction;
use App\Models\Discount;
use App\Models\DiscountTarget;

class SyncDiscountTargetsAction extends BaseAction
{
  /**
   * Đồng bộ các mối quan hệ targets của mã giảm giá
   *
   * @param Discount $discount Mã giảm giá cần đồng bộ mối quan hệ
   * @param array $targetIds Mảng chứa các target_id cần đồng bộ
   * @return bool
   */
  public function execute(...$args): bool
  {
    [$discount, $targetIds] = $args;

    // Xóa targets hiện tại
    DiscountTarget::where('discount_id', $discount->id)->delete();

    // Thêm targets mới nếu là giảm giá theo sách
    if ($discount->target_type === 'book' && !empty($targetIds)) {
      $records = [];
      foreach ($targetIds as $targetId) {
        $records[] = [
          'discount_id' => $discount->id,
          'target_id' => $targetId,
        ];
      }

      // Sử dụng insert batch để tối ưu hiệu suất
      if (!empty($records)) {
        DiscountTarget::insert($records);
      }
    }

    return true;
  }

  /**
   * Trích xuất các target_ids từ request data
   *
   * @param array $requestData
   * @return array
   */
  public function extractTargetsFromRequest(array $requestData): array
  {
    $targetIds = [];

    // Trích xuất targets nếu có
    $targets = data_get($requestData, 'data.relationships.targets.data');
    if (!empty($targets)) {
      $targetIds = collect($targets)->pluck('id')->toArray();
    }

    return $targetIds;
  }
}
