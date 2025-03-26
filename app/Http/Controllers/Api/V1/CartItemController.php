<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddToCartRequest;
use App\Http\Resources\V1\CartItemCollection;
use App\Http\Resources\V1\CartItemResource;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Auth;
use Illuminate\Http\JsonResponse;


class CartItemController extends Controller
{
	/**
	 * Get all cart items of the customer.
	 *
	 * @return JsonResponse
	 * @group Cart
	 */
	public function index()
	{
		$user = AuthUtils::user();

		$cartItems = $user->cartItems()
						  ->with('book')
						  ->get();

		return ResponseUtils::success([
			'cart_items' => new CartItemCollection($cartItems),
		]);
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
		$user = AuthUtils::user();
		$validatedData = $request->validated();

		if ($user->isBookInCart($validatedData['book_id'])) {
			// Update the quantity of the existing cart item
			$user->booksInCart()->updateExistingPivot($validatedData['book_id'], [
				'quantity' => $validatedData['quantity'],
			]);

			return ResponseUtils::success([
				'cart_item' => new CartItemResource(
					$user->getCartItemByBook($validatedData['book_id'])
				),
			], ResponseMessage::UPDATED_CART_ITEM->value);
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
		$user = AuthUtils::user();
		$validatedData = $request->validated();

		if ($user->isBookInCart($validatedData['book_id'])) {
			return ResponseUtils::badRequest(ResponseMessage::ALREADY_IN_CART->value);
		} else {
			// Create a new cart item for the user
			$user->booksInCart()->attach($validatedData['book_id'], [
				'quantity' => $validatedData['quantity'],
			]);

			return ResponseUtils::created([
				'cart_item' => new CartItemResource(
					$user->getCartItemByBook($validatedData['book_id'])
				),
			], ResponseMessage::ADDED_TO_CART->value);
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
		$user = AuthUtils::user();

		if (!$user->isBookInCart($book_id)) {
			return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_CART_ITEM->value);
		}

		$user->booksInCart()->detach($book_id);

		return ResponseUtils::noContent(ResponseMessage::REMOVED_FROM_CART->value);
	}
}
