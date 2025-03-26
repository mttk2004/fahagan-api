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
use App\Traits\ApiResponses;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class DiscountController extends Controller
{
	use ApiResponses;


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
			return collect($targetsData)->map(function($target) {
				$targetType = 'App\Models\\' . ucfirst($target['type']);
				$targetType::findOrFail($target['id']);

				return ['target_type' => $targetType, 'target_id' => $target['id']];
			});
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_TARGET_OBJECT->value);
		}
	}

	/**
	 * Get all discounts
	 *
	 * @param Request $request
	 *
	 * @return DiscountCollection|JsonResponse
	 * @group Discounts
	 */
	public function index(Request $request)
	{
		$user = Auth::guard('sanctum')->user();
		if (!$user->hasPermissionTo('view_discounts')) {
			return $this->unauthorized();
		}

		$discountSort = new DiscountSort($request);
		$discounts = $discountSort->apply(Discount::query())->paginate();

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
		$validatedData = $request->validated();
		$targetsData = $validatedData['data']['relationships']['targets'];

		$targets = $this->validateAndMapTargets($targetsData);
		if ($targets instanceof JsonResponse) {
			return $targets;
		}

		$discount = Discount::create($validatedData['data']['attributes']);
		$discount->targets()->createMany($targets);

		return $this->ok(ResponseMessage::CREATED_DISCOUNT->value, [
			'discount' => new DiscountResource($discount),
		]);
	}

	/**
	 * Get a discount
	 *
	 * @param         $discount_id
	 *
	 * @return DiscountResource|JsonResponse
	 * @group Discounts
	 */
	public function show($discount_id)
	{
		$user = Auth::guard('sanctum')->user();
		if (!$user->hasPermissionTo('view_discounts')) {
			return $this->unauthorized();
		}

		try {
			return new DiscountResource(Discount::findOrFail($discount_id));
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
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
			$validatedData = $request->validated();
			$discountData = $validatedData['data']['attributes'];

			if (isset($validatedData['data']['relationships']['targets'])) {
				$targetsData = $validatedData['data']['relationships']['targets'];

				$targets = $this->validateAndMapTargets($targetsData);
				if ($targets instanceof JsonResponse) {
					return $targets;
				}

				$discount->targets()->delete();
				$discount->targets()->createMany($targets);
			}

			$discount->update($discountData);

			return $this->ok(ResponseMessage::UPDATED_DISCOUNT->value, [
				'discount' => new DiscountResource($discount),
			]);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
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
		$user = Auth::guard('sanctum')->user();
		if (!$user->hasPermissionTo('delete_discounts')) {
			return $this->unauthorized();
		}

		try {
			Discount::findOrFail($discount_id)->delete();

			return $this->ok(ResponseMessage::DELETED_DISCOUNT->value);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_DISCOUNT->value);
		}
	}
}
