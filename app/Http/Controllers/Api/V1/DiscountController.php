<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DiscountStoreRequest;
use App\Http\Requests\V1\DiscountUpdateRequest;
use App\Http\Resources\V1\DiscountCollection;
use App\Http\Resources\V1\DiscountResource;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Discount;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DiscountController extends Controller
{
    /**
     * Validate and map targets
     *
     * @param array $targetsData
     *
     * @return Collection|JsonResponse
     */
    private function validateAndMapTargets(array $targetsData)
    {
        try {
            return collect($targetsData)->map(function ($target) {
                $targetType = 'App\Models\\' . ucfirst($target['type']);
                $targetType::findOrFail($target['id']);

                return ['target_type' => $targetType, 'target_id' => $target['id']];
            });
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_TARGET_OBJECT->value);
        }
    }

    /**
     * Get all discounts
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @group Discounts
     */
    public function index(Request $request)
    {
        if (! AuthUtils::userCan('view_discounts')) {
            return ResponseUtils::forbidden();
        }

        $discountSort = new DiscountSort($request);
        $discounts = $discountSort->apply(Discount::query())->paginate();

        return ResponseUtils::success([
            'discounts' => new DiscountCollection($discounts),
        ]);
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
        $validatedData = $request->validated()['data'];

        $targetsData = $validatedData['relationships']['targets'];
        $targets = $this->validateAndMapTargets($targetsData);
        if ($targets instanceof JsonResponse) {
            return $targets;
        }

        $discount = Discount::create($validatedData['attributes']);
        $discount->targets()->createMany($targets);

        return ResponseUtils::created([
            'discount' => new DiscountResource($discount),
        ], ResponseMessage::CREATED_DISCOUNT->value);
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
            return ResponseUtils::success([
                'discount' => new DiscountResource(Discount::findOrFail($discount_id)),
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
            $discount = Discount::findOrFail($discount_id);
            $validatedData = $request->validated()['data'];

            // Update targets
            $targetsData = $validatedData['relationships']['targets'] ?? null;
            if ($targetsData) {
                $targets = $this->validateAndMapTargets($targetsData);
                if ($targets instanceof JsonResponse) {
                    return $targets;
                }

                $discount->targets()->delete();
                $discount->targets()->createMany($targets);
            }

            // Update discount data
            $discountData = $validatedData['attributes'] ?? null;
            if ($discountData) {
                $discount->update($discountData);
            }

            return ResponseUtils::success([
                'discount' => new DiscountResource($discount),
            ], ResponseMessage::UPDATED_DISCOUNT->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
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
            Discount::findOrFail($discount_id)->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_DISCOUNT->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
        }
    }
}
