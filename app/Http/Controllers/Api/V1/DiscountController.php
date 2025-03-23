<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DiscountStoreRequest;
use App\Http\Resources\V1\DiscountCollection;
use App\Http\Resources\V1\DiscountResource;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Discount;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function PHPUnit\Framework\throwException;


class DiscountController extends Controller
{
	use ApiResponses;


	/**
	 * Get all discounts
	 *
	 * @param Request $request
	 *
	 * @return DiscountCollection
	 * @group Discounts
	 */
	public function index(Request $request)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('view_discounts')) {
			$this->forbidden();
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

		try {
			$targets = collect($targetsData)->map(function($target) {
				$targetType = 'App\Models\\' . ucfirst($target['type']);
				$targetType::findOrFail($target['id']);

				return ['target_type' => $targetType, 'target_id' => $target['id']];
			});
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_TARGET_OBJECT->value);
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
	 * @param Request $request
	 * @param         $discount_id
	 *
	 * @return DiscountResource|JsonResponse
	 * @group Discounts
	 */
	public function show(Request $request, $discount_id)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('view_discounts')) {
			$this->forbidden();
		}

		try {
			return new DiscountResource(Discount::findOrFail($discount_id));
		} catch (ModelNotFoundException) {
			return $this->notFound('Giảm giá không tồn tại.');
		}
	}

	/**
	 * Update a discount
	 *
	 * @param Request  $request
	 * @param Discount $discount
	 *
	 * @return void
	 * @group Discounts
	 */
	public function update(Request $request, Discount $discount) {}

	/**
	 * Delete a discount
	 *
	 * @param Discount $discount
	 *
	 * @return void
	 * @group Discounts
	 */
	public function destroy(Discount $discount) {}
}
