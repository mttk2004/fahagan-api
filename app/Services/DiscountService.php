<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\Discount\DiscountDTO;
use App\Filters\DiscountFilter;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Discount;
use App\Models\DiscountTarget;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscountService extends BaseService
{
  /**
   * DiscountService constructor.
   */
  public function __construct()
  {
    $this->model = new Discount();
    $this->filterClass = DiscountFilter::class;
    $this->sortClass = DiscountSort::class;
    $this->with = ['targets'];
  }

  /**
   * Lấy danh sách mã giảm giá
   */
  public function getAllDiscounts(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
  {
    return $this->getAll($request, $perPage);
  }

  /**
   * Tạo mã giảm giá mới hoặc khôi phục mã giảm giá đã bị xóa mềm nếu đã tồn tại với cùng tên
   *
   * @throws ValidationException
   * @throws Exception
   */
  public function createDiscount(DiscountDTO $discountDTO): Discount
  {
    $data = $discountDTO->toArray();

    // Kiểm tra xem có đủ thông tin cần thiết hay không
    if (! isset($data['name'])) {
      throw ValidationException::withMessages([
        'data.attributes.name' => ['Tên mã giảm giá là bắt buộc.'],
      ]);
    }

    // Kiểm tra xem có mã giảm giá nào đã bị xóa mềm với cùng tên hay không
    $trashedDiscount = Discount::withTrashed()
      ->where('name', $data['name'])
      ->onlyTrashed()
      ->first();

    if ($trashedDiscount) {
      try {
        DB::beginTransaction();

        // Khôi phục mã giảm giá đã bị xóa mềm
        $trashedDiscount->restore();

        // Cập nhật thông tin từ DTO
        $trashedDiscount->update($data);

        // Xử lý relations nếu có
        if (!empty($discountDTO->target_ids)) {
          $this->syncTargets($trashedDiscount, $discountDTO->target_ids);
        }

        DB::commit();

        return $trashedDiscount->fresh($this->with);
      } catch (Exception $e) {
        DB::rollBack();
        throw $e;
      }
    }

    // Kiểm tra xem có mã giảm giá nào (chưa bị xóa) với cùng tên hay không
    $existingDiscount = Discount::where('name', $data['name'])->first();

    if ($existingDiscount) {
      throw ValidationException::withMessages([
        'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
      ]);
    }

    // Tạo mã giảm giá với targets
    $relations = [];
    if (!empty($discountDTO->target_ids)) {
      $this->prepareTargetRelations($discountDTO, $relations);
    }

    try {
      return $this->create($discountDTO, $relations);
    } catch (QueryException $e) {
      // Nếu là lỗi ràng buộc duy nhất, chuyển nó thành ValidationException
      if ($e->getCode() == 23000 && strpos($e->getMessage(), 'discounts_name_unique') !== false) {
        throw ValidationException::withMessages([
          'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
        ]);
      }
      throw $e;
    }
  }

  /**
   * Lấy thông tin chi tiết mã giảm giá
   *
   * @throws ModelNotFoundException
   */
  public function getDiscountById(string|int $discountId): Discount
  {
    return $this->getById($discountId);
  }

  /**
   * Cập nhật mã giảm giá
   *
   * @throws ModelNotFoundException
   * @throws ValidationException
   * @throws Exception
   */
  public function updateDiscount(string|int $discountId, DiscountDTO $discountDTO, array $originalRequest = []): Discount
  {
    try {
      // Tìm mã giảm giá hiện tại
      $discount = $this->getById($discountId);

      // Lấy dữ liệu cập nhật từ DTO
      $data = $discountDTO->toArray();

      // Kiểm tra xem có tên mã giảm giá mới, và nếu có, đảm bảo rằng nó là duy nhất
      if (isset($data['name']) && $data['name'] !== $discount->name) {
        $existingDiscount = Discount::where('name', $data['name'])
          ->where('id', '!=', $discountId)
          ->first();

        if ($existingDiscount) {
          throw ValidationException::withMessages([
            'data.attributes.name' => ['Đã tồn tại mã giảm giá với tên này. Vui lòng sử dụng tên khác.'],
          ]);
        }
      }

      // Chuẩn bị relations
      $relations = [];

      // Kiểm tra xem relationships targets có trong request gốc hay không
      $hasTargetsInRequest = isset($originalRequest['data']['relationships']['targets']);

      // Chỉ đồng bộ targets khi relationships targets có trong request
      if ($hasTargetsInRequest && !empty($discountDTO->target_ids)) {
        $this->prepareTargetRelations($discountDTO, $relations);
      }

      return $this->update($discountId, $discountDTO, $relations);
    } catch (ValidationException $e) {
      throw $e;
    } catch (Exception $e) {
      throw $e;
    }
  }

  /**
   * Xóa mã giảm giá
   *
   * @throws ModelNotFoundException
   */
  public function deleteDiscount(string|int $discountId): Discount
  {
    // Tìm discount trước khi xóa
    $discount = $this->getById($discountId);

    // Thực hiện xóa
    $this->delete($discountId);

    // Trả về đối tượng đã tìm thấy trước đó
    return $discount;
  }

  /**
   * Chuẩn bị target relations cho discount
   *
   * @param DiscountDTO $discountDTO
   * @param array &$relations
   */
  private function prepareTargetRelations(DiscountDTO $discountDTO, array &$relations): void
  {
    // Đặt targets vào relations
    // Lưu ý: Chúng ta sẽ cần một phương thức syncRelations đặc biệt cho targets
    $relations['targets'] = $discountDTO->target_ids;
  }

  /**
   * Đồng bộ targets với mã giảm giá
   *
   * @param \Illuminate\Database\Eloquent\Model $resource
   * @param array $relations
   * @return void
   */
  protected function syncRelations(\Illuminate\Database\Eloquent\Model $resource, array $relations): void
  {
    foreach ($relations as $relation => $ids) {
      if ($relation === 'targets') {
        // Xử lý đặc biệt cho targets
        $this->syncTargets($resource, $ids);
      } else if (method_exists($resource, $relation)) {
        // Xử lý các mối quan hệ khác
        $resource->$relation()->sync($ids);
      }
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
    foreach ($targetIds as $targetId) {
      DiscountTarget::create([
        'discount_id' => $discount->id,
        'target_id' => $targetId,
        'target_type' => 'book', // Mặc định là book, cần cập nhật sau với giá trị thực tế
      ]);
    }
  }

  /**
   * Find a trashed resource based on unique attributes
   *
   * @param BaseDTO $dto
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  protected function findTrashed(BaseDTO $dto): ?\Illuminate\Database\Eloquent\Model
  {
    // Đảm bảo DTO là kiểu DiscountDTO trước khi tiếp tục
    if (!($dto instanceof DiscountDTO) || !isset($dto->name)) {
      return null;
    }

    return Discount::withTrashed()
      ->where('name', $dto->name)
      ->onlyTrashed()
      ->first();
  }

  /**
   * Get the message when trying to restore a resource that is not deleted
   *
   * @return string
   */
  protected function getResourceNotDeletedMessage(): string
  {
    return "Mã giảm giá này chưa bị xóa.";
  }
}
