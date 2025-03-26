<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddToCartRequest;
use App\Http\Resources\V1\CartItemCollection;
use App\Http\Resources\V1\CartItemResource;
use App\Traits\ApiResponses;
use Auth;
use Illuminate\Http\JsonResponse;


class CartItemController extends Controller
{
	use ApiResponses;


	/**
	 * Get all cart items of the customer.
	 *
	 * @return CartItemCollection
	 * @group Cart
	 */
	public function index()
	{
		$user = Auth::guard('sanctum')->user();

		$cartItems = $user->cartItems()
						  ->with('book')
						  ->get();

		return new CartItemCollection($cartItems);
	}

	/**
	 * Update the quantity of a cart item or add a new book to the cart if it doesn't exist.
	 *
	 * @param AddToCartRequest $request
	 *
	 * @return JsonResponse
	 * @group Cart
	 */
	public function updateCartItemQuantity(AddToCartRequest $request)
	{
		$user = Auth::guard('sanctum')->user();
		$validatedData = $request->validated();

		if ($user->isBookInCart($validatedData['book_id'])) {
			// Update the quantity of the existing cart item
			$user->booksInCart()->updateExistingPivot($validatedData['book_id'], [
				'quantity' => $validatedData['quantity'],
			]);

			return $this->ok(ResponseMessage::UPDATED_CART_ITEM->value, [
				'cart_item' => new CartItemResource(
					$user->getCartItemByBook($validatedData['book_id'])
				),
			]);
		} else {
			return $this->addToCart($request);
		}
	}

	/**
	 * Add a book to the cart or return an error if it already exists.
	 *
	 * @param AddToCartRequest $request
	 *
	 * @return JsonResponse
	 * @group Cart
	 */
	public function addToCart(AddToCartRequest $request)
	{
		$user = Auth::guard('sanctum')->user();
		$validatedData = $request->validated();

		if ($user->isBookInCart($validatedData['book_id'])) {
			return $this->error(ResponseMessage::ALREADY_IN_CART->value, 409);
		} else {
			// Create a new cart item for the user
			$user->booksInCart()->attach($validatedData['book_id'], [
				'quantity' => $validatedData['quantity'],
			]);

			return $this->ok(ResponseMessage::ADDED_TO_CART->value, [
				'cart_item' => new CartItemResource(
					$user->getCartItemByBook($validatedData['book_id'])
				),
			]);
		}
	}

	/**
	 * Remove a book from the cart.
	 *
	 * @param int $book_id
	 *
	 * @return JsonResponse
	 * @group Cart
	 */
	public function removeFromCart(int $book_id)
	{
		$user = Auth::guard('sanctum')->user();

		if (!$user->isBookInCart($book_id)) {
			return $this->notFound(ResponseMessage::NOT_FOUND_CART_ITEM->value);
		}

		$user->booksInCart()->detach($book_id);

		return $this->ok(ResponseMessage::REMOVED_FROM_CART->value);
	}
}
