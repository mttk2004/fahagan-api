<?php

namespace App\Services;

use App\Actions\Discounts\CreateDiscountAction;
use App\Actions\Discounts\DeleteDiscountAction;
use App\Actions\Discounts\RestoreDiscountAction;
use App\Actions\Discounts\SyncDiscountTargetsAction;
use App\Actions\Discounts\UpdateDiscountAction;
use App\Actions\Discounts\ValidateDiscountAction;
use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\DiscountDTO;
use App\Http\Filters\V1\DiscountFilter;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Book;
use App\Models\Discount;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Throwable;

class DiscountService extends BaseService
{
  /**
   * Actions
   */
  protected CreateDiscountAction $createDiscountAction;

  protected RestoreDiscountAction $restoreDiscountAction;

  protected UpdateDiscountAction $updateDiscountAction;

  protected DeleteDiscountAction $deleteDiscountAction;

  protected ValidateDiscountAction $validateDiscountAction;

  protected SyncDiscountTargetsAction $syncDiscountTargetsAction;

  /**
   * DiscountService constructor.
   */
  public function __construct()
  {
    $this->model = new Discount;
    $this->filterClass = DiscountFilter::class;
    $this->sortClass = DiscountSort::class;
    $this->with = ['targets'];

    // Khởi tạo các action
    $this->createDiscountAction = new CreateDiscountAction;
    $this->restoreDiscountAction = new RestoreDiscountAction;
    $this->updateDiscountAction = new UpdateDiscountAction;
    $this->deleteDiscountAction = new DeleteDiscountAction;
    $this->validateDiscountAction = new ValidateDiscountAction;
    $this->syncDiscountTargetsAction = new SyncDiscountTargetsAction;
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
   * @throws Throwable
   */
  public function createDiscount(DiscountDTO $discountDTO): Discount
  {
    // Xác thực dữ liệu
    $this->validateDiscountAction->execute($discountDTO);

    try {
      // Tạo mã giảm giá mới
      return $this->createDiscountAction->execute($discountDTO, $this->with);
    } catch (QueryException $e) {
      // Nếu là lỗi ràng buộc duy nhất, chuyển nó thành ValidationException
      if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'discounts_name_unique')) {
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
  public function getDiscountById(string|int $discountId): Model
  {
    return $this->getById($discountId);
  }

  /**
   * Cập nhật mã giảm giá
   *
   * @throws ModelNotFoundException
   * @throws ValidationException
   * @throws Exception|Throwable
   */
  public function updateDiscount(string|int $discountId, DiscountDTO $discountDTO, array $originalRequest = []): Discount
  {
    try {
      // Tìm mã giảm giá hiện tại
      $discount = $this->getById($discountId);

      // Xác thực dữ liệu cập nhật
      $this->validateDiscountAction->execute($discountDTO, true, $discount);

      // Lấy dữ liệu cập nhật từ DTO
      $data = $discountDTO->toArray();

      // Chuẩn bị relations
      $relations = [];

      // Chỉ đồng bộ targets khi là giảm giá theo sách
      if (isset($data['target_type']) ? $data['target_type'] === 'book' : $discount->target_type === 'book') {
        // Kiểm tra xem relationships targets có trong request gốc hay không
        $hasTargetsInRequest = isset($originalRequest['data']['relationships']['targets']);

        // Chỉ đồng bộ targets khi relationships targets có trong request
        if ($hasTargetsInRequest) {
          $targetIds = $this->syncDiscountTargetsAction->extractTargetsFromRequest($originalRequest);
          if (! empty($targetIds)) {
            $relations['targets'] = $targetIds;
          }
        }
      }

      // Thực hiện cập nhật
      return $this->updateDiscountAction->execute($discount, $data, $relations);
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
   * @throws Throwable
   */
  public function deleteDiscount(string|int $discountId): Model
  {
    // Tìm discount trước khi xóa
    $discount = $this->getById($discountId);

    // Thực hiện xóa
    $this->deleteDiscountAction->execute($discount);

    // Trả về đối tượng đã tìm thấy trước đó
    return $discount;
  }

  /**
   * Tính giá sau khi áp dụng giảm giá cho một sách
   */
  public function calculateDiscountedPrice(Book $book): float
  {
    $originalPrice = $book->price;
    $activeDiscounts = $this->getActiveBookDiscounts($book->id);

    if ($activeDiscounts->isEmpty()) {
      return $originalPrice;
    }

    // Lọc các discount không đáp ứng điều kiện min_purchase_amount
    $validDiscounts = $activeDiscounts->filter(function ($discount) use ($originalPrice) {
      if ($discount->min_purchase_amount > 0 && $originalPrice < $discount->min_purchase_amount) {
        return false;
      }

      return true;
    });

    if ($validDiscounts->isEmpty()) {
      return $originalPrice;
    }

    // Áp dụng discount cao nhất
    $highestDiscount = $validDiscounts->sortByDesc(function ($discount) use ($originalPrice) {
      if ($discount->discount_type === 'fixed') {
        // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
        return $discount->max_discount_amount
          ? min($discount->discount_value, $discount->max_discount_amount)
          : $discount->discount_value;
      } else {
        // Tính toán giá trị thực tế sau khi áp dụng giới hạn max_discount_amount
        $discountAmount = ($originalPrice * $discount->discount_value) / 100;

        return $discount->max_discount_amount
          ? min($discountAmount, $discount->max_discount_amount)
          : $discountAmount;
      }
    })->first();

    if (! $highestDiscount) {
      return $originalPrice;
    }

    if ($highestDiscount->discount_type === 'fixed') {
      $discountValue = $highestDiscount->max_discount_amount
        ? min($highestDiscount->discount_value, $highestDiscount->max_discount_amount)
        : $highestDiscount->discount_value;

      return max(0, $originalPrice - $discountValue);
    } else {
      $discountAmount = ($originalPrice * $highestDiscount->discount_value) / 100;
      $discountValue = $highestDiscount->max_discount_amount
        ? min($discountAmount, $highestDiscount->max_discount_amount)
        : $discountAmount;

      return max(0, $originalPrice - $discountValue);
    }
  }

  /**
   * Lấy tất cả mã giảm giá đang hoạt động cho một sách
   *
   * @param int|string $bookId
   *
   * @return Collection
   */
  public function getActiveBookDiscounts(int|string $bookId): Collection
  {
    $now = now();

    return Discount::whereHas('targets', function ($query) use ($bookId) {
      $query->where('target_id', $bookId);
    })
      ->where('target_type', 'book')
      ->where('is_active', true)
      ->where('start_date', '<=', $now)
      ->where('end_date', '>=', $now)
      ->get();
  }

  /**
   * Get the message when trying to restore a resource that is not deleted
   */
  protected function getResourceNotDeletedMessage(): string
  {
    return 'Mã giảm giá này chưa bị xóa.';
  }
}
