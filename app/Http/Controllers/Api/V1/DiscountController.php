<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Discount\DiscountDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DiscountStoreRequest;
use App\Http\Requests\V1\DiscountUpdateRequest;
use App\Http\Resources\V1\DiscountCollection;
use App\Http\Resources\V1\DiscountResource;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Discount;
use App\Services\DiscountService;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DiscountController extends Controller
{
    use HandlePagination;

    private DiscountService $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Get all discounts
     *
     * @param Request $request
     *
     * @return JsonResponse|DiscountCollection
     * @group Discounts
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
     * @param DiscountStoreRequest $request
     *
     * @return JsonResponse
     * @group Discounts
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
        } catch (ValidationException $e) {
            return ResponseUtils::validationError($e->validator->errors());
        } catch (\Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Get a discount
     *
     * @param         $discount_id
     *
     * @return JsonResponse
     * @group Discounts
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
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
        }
    }

    /**
     * Update a discount
     *
     * @param DiscountUpdateRequest $request
     * @param                       $discount_id
     *
     * @return JsonResponse
     * @group Discounts
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
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
        } catch (ValidationException $e) {
            return ResponseUtils::validationError($e->validator->errors());
        } catch (\Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Delete a discount
     *
     * @param         $discount_id
     *
     * @return JsonResponse
     * @group Discounts
     */
    public function destroy($discount_id)
    {
        if (! AuthUtils::userCan('delete_discounts')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->discountService->deleteDiscount($discount_id);

            return ResponseUtils::noContent(ResponseMessage::DELETED_DISCOUNT->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
        }
    }
}
