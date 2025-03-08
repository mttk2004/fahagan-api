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
	public function index(Request $request)
	{
		$discountSort = new DiscountSort($request);
		$discounts = $discountSort->apply(Discount::query())->paginate();

		return new DiscountCollection($discounts);
	}

	public function store(Request $request) {}

	public function show(Discount $discount) {
		return new DiscountResource($discount);
	}

	public function update(Request $request, Discount $discount) {}

	public function destroy(Discount $discount) {}
}
