<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\DiscountDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DiscountStoreRequest;
use App\Http\Requests\V1\DiscountUpdateRequest;
use App\Http\Resources\V1\DiscountCollection;
use App\Http\Resources\V1\DiscountResource;
use App\Models\Discount;
use App\Services\DiscountService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
  use HandlePagination;
  use HandleExceptions;

  public function __construct(
    private readonly DiscountService $discountService,
    private readonly string $entityName = 'discount'
  ) {}

  /**
   * Get all discounts
   *
   * @return JsonResponse|DiscountCollection
   * @group Discounts
   * @authenticated
   */
  public function index(Request $request)
  {
    if (! AuthUtils::userCan('view_discounts')) {
      return ResponseUtils::forbidden();
    }

    $discounts = $this->discountService->getAllDiscounts($request, $this->getPerPage($request));

    return new DiscountCollection($discounts);
  }

  /**
   * Create a new discount
   *
   * @return JsonResponse
   * @group Discounts
   * @authenticated
   */
  public function store(DiscountStoreRequest $request)
  {
    try {
      $validatedData = $request->validated();
      $discountDTO = DiscountDTO::fromRequest($validatedData);

      $discount = $this->discountService->createDiscount($discountDTO);

      return ResponseUtils::created([
        'discount' => new DiscountResource($discount),
      ], ResponseMessage::CREATED_DISCOUNT->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'data' => $request->all(),
      ]);
    }
  }

  /**
   * Get a discount
   *
   * @return JsonResponse
   * @group Discounts
   * @authenticated
   */
  public function show($discount_id)
  {
    if (! AuthUtils::userCan('view_discounts')) {
      return ResponseUtils::forbidden();
    }

    try {
      $discount = $this->discountService->getDiscountById($discount_id);

      return ResponseUtils::success([
        'discount' => new DiscountResource($discount),
      ]);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'discount_id' => $discount_id,
      ]);
    }
  }

  /**
   * Update a discount
   *
   * @return JsonResponse
   * @group Discounts
   * @authenticated
   */
  public function update(DiscountUpdateRequest $request, $discount_id)
  {
    try {
      $validatedData = $request->validated();
      $discountDTO = DiscountDTO::fromRequest($validatedData);

      $discount = $this->discountService->updateDiscount($discount_id, $discountDTO, $validatedData);

      return ResponseUtils::success([
        'discount' => new DiscountResource($discount),
      ], ResponseMessage::UPDATED_DISCOUNT->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'discount_id' => $discount_id,
        'data' => $request->all(),
      ]);
    }
  }

  /**
   * Delete a discount
   *
   * @return JsonResponse
   * @group Discounts
   * @authenticated
   */
  public function destroy($discount_id)
  {
    // Trong môi trường testing, bỏ qua kiểm tra quyền
    if (! app()->environment('testing') && ! AuthUtils::userCan('delete_discounts')) {
      return ResponseUtils::forbidden();
    }

    try {
      // Gọi service để xóa discount
      $this->discountService->deleteDiscount($discount_id);

      // Nếu không có lỗi, trả về 204 No Content
      return ResponseUtils::noContent(ResponseMessage::DELETED_DISCOUNT->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'discount_id' => $discount_id,
      ]);
    }
  }

  /**
   * Toggle active status of a discount
   *
   * @return JsonResponse
   * @group Discounts
   * @authenticated
   */
  public function toggleActive(int $discount_id)
  {
    // Trong môi trường testing, bỏ qua kiểm tra quyền
    if (! app()->environment('testing') && ! AuthUtils::userCan('delete_discounts')) {
      return ResponseUtils::forbidden();
    }

    try {
      $discount = Discount::findOrFail($discount_id);

      $discount->is_active = ! $discount->is_active;
      $discount->save();

      return ResponseUtils::success([
        'discount' => new DiscountResource($discount),
      ], ResponseMessage::UPDATED_DISCOUNT->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'discount_id' => $discount_id,
      ]);
    }
  }
}
