<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddToCartRequest;
use App\Traits\ApiResponses;


class CartItemController extends Controller
{
	use ApiResponses;


	public function addToCart(AddToCartRequest $request)
	{
		$user = $request->user();
		$validatedData = $request->validated();

		if ($user->cartItemExists($request->book_id)) {
			// Update the quantity of the existing cart item
			$user->cartItems()
				 ->where('book_id', $validatedData['book_id'])
				 ->update([
					 'quantity' => \DB::raw('quantity + ' . $validatedData['quantity'])
				 ]);

			return $this->ok('Đã cập nhật số lượng sách trong giỏ hàng.');
		} else {
			// Create a new cart item
			$user->cartItems()->create($validatedData);

			return $this->ok('Đã thêm sách vào giỏ hàng.');
		}
	}
}
