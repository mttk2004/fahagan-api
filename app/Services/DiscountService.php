<?php

namespace App\Services;

use App\Actions\Discounts\CreateDiscountAction;
use App\Actions\Discounts\DeleteDiscountAction;
use App\Actions\Discounts\FindTrashedDiscountAction;
use App\Actions\Discounts\RestoreDiscountAction;
use App\Actions\Discounts\SyncDiscountTargetsAction;
use App\Actions\Discounts\UpdateDiscountAction;
use App\Actions\Discounts\ValidateDiscountAction;
use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\Discount\DiscountDTO;
use App\Filters\DiscountFilter;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Book;
use App\Models\Discount;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class DiscountService extends BaseService
{
  /**
   * Actions
   */
  protected CreateDiscountAction $createDiscountAction;
  protected FindTrashedDiscountAction $findTrashedDiscountAction;
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
    $this->model = new Discount();
    $this->filterClass = DiscountFilter::class;
    $this->sortClass = DiscountSort::class;
    $this->with = ['targets'];

    // Khởi tạo các action
    $this->createDiscountAction = new CreateDiscountAction();
    $this->findTrashedDiscountAction = new FindTrashedDiscountAction();
    $this->restoreDiscountAction = new RestoreDiscountAction();
    $this->updateDiscountAction = new UpdateDiscountAction();
    $this->deleteDiscountAction = new DeleteDiscountAction();
    $this->validateDiscountAction = new ValidateDiscountAction();
    $this->syncDiscountTargetsAction = new SyncDiscountTargetsAction();
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
    // Xác thực dữ liệu
    $this->validateDiscountAction->execute($discountDTO);

    // Kiểm tra mã giảm giá đã xóa mềm
    $trashedDiscount = $this->findTrashedDiscountAction->execute($discountDTO);

    if ($trashedDiscount) {
      // Khôi phục mã giảm giá đã xóa
      return $this->restoreDiscountAction->execute($trashedDiscount, $discountDTO);
    }

    try {
      // Tạo mã giảm giá mới
      return $this->createDiscountAction->execute($discountDTO, $this->with);
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
          if (!empty($targetIds)) {
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
   */
  public function deleteDiscount(string|int $discountId): Discount
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
   *
   * @param Book $book
   * @return float
   */
  public function calculateDiscountedPrice(Book $book): float
  {
    $originalPrice = $book->price;
    $activeDiscounts = $this->getActiveBookDiscounts($book->id);

    if ($activeDiscounts->isEmpty()) {
      return $originalPrice;
    }

    // Áp dụng discount cao nhất
    $highestDiscount = $activeDiscounts->sortByDesc(function ($discount) {
      if ($discount->discount_type === 'fixed') {
        return $discount->discount_value;
      } else {
        return $discount->discount_value; // Percent
      }
    })->first();

    if ($highestDiscount->discount_type === 'fixed') {
      return max(0, $originalPrice - $highestDiscount->discount_value);
    } else {
      $discountAmount = ($originalPrice * $highestDiscount->discount_value) / 100;
      return max(0, $originalPrice - $discountAmount);
    }
  }

  /**
   * Lấy tất cả mã giảm giá đang hoạt động cho một sách
   *
   * @param int|string $bookId
   * @return \Illuminate\Support\Collection
   */
  public function getActiveBookDiscounts($bookId)
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
   * Find a trashed resource based on unique attributes
   *
   * @param BaseDTO $dto
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  protected function findTrashed(BaseDTO $dto): ?\Illuminate\Database\Eloquent\Model
  {
    // Đảm bảo DTO là kiểu DiscountDTO trước khi tiếp tục
    if (! ($dto instanceof DiscountDTO) || ! isset($dto->name)) {
      return null;
    }

    return $this->findTrashedDiscountAction->execute($dto);
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
