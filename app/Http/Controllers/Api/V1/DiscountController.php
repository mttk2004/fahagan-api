<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DiscountCollection;
use App\Http\Resources\V1\DiscountResource;
use App\Http\Sorts\V1\DiscountSort;
use App\Models\Discount;
use Illuminate\Http\Request;


class DiscountController extends Controller
{
	/**
	 * Get all discounts
	 *
	 * @param Request $request
	 * @return DiscountCollection
	 * @group Discounts
	 */
	public function index(Request $request)
	{
		$discountSort = new DiscountSort($request);
		$discounts = $discountSort->apply(Discount::query())->paginate();

		return new DiscountCollection($discounts);
	}

	/**
	 * Create a new discount
	 *
	 * @param Request $request
	 * @return void
	 * @group Discounts
	 */
	public function store(Request $request) {}

	/**
	 * Get a discount
	 *
	 * @param Discount $discount
	 * @return DiscountResource
	 * @group Discounts
	 */
	public function show(Discount $discount) {
		return new DiscountResource($discount);
	}

	/**
	 * Update a discount
	 *
	 * @param Request $request
	 * @param Discount $discount
	 * @return void
	 * @group Discounts
	 */
	public function update(Request $request, Discount $discount) {}

	/**
	 * Delete a discount
	 *
	 * @param Discount $discount
	 * @return void
	 * @group Discounts
	 */
	public function destroy(Discount $discount) {}
}
