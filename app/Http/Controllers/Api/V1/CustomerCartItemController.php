<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CartItemDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddToCartRequest;
use App\Http\Resources\V1\CartItemCollection;
use App\Http\Resources\V1\CartItemResource;
use App\Services\CartItemService;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;

class CustomerCartItemController extends Controller
{
    public function __construct(
        private readonly CartItemService $cartItemService
    ) {
    }

    /**
     * Get all cart items of the customer.
     *
     * @group Customer.Cart
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        $user = AuthUtils::user();
        $cartItems = $this->cartItemService->getCartItems($user);

        return ResponseUtils::success([
            'cart_items' => new CartItemCollection($cartItems),
        ]);
    }

    /**
     * Update the quantity of a cart item or add a new book to the cart if it doesn't exist.
     *
     *
     * @group Customer.Cart
     *
     * @authenticated
     */
    public function updateCartItemQuantity(AddToCartRequest $request): JsonResponse
    {
        $user = AuthUtils::user();
        $cartItemDTO = CartItemDTO::fromRequest($request->validated());

        try {
            // Kiểm tra xem sách đã tồn tại trong giỏ hàng chưa
            $cartItem = $this->cartItemService->findCartItem($user, $cartItemDTO->book_id);

            if ($cartItem) {
                // Nếu đã tồn tại, cập nhật số lượng
                $cartItem = $this->cartItemService->updateCartItemQuantity($user, $cartItemDTO);
                $message = ResponseMessage::UPDATED_CART_ITEM->value;
            } else {
                // Nếu chưa tồn tại, thêm mới
                $cartItem = $this->cartItemService->addToCartNoChecking($user, $cartItemDTO);
                $message = ResponseMessage::ADDED_TO_CART->value;
            }

            return ResponseUtils::success([
                'cart_item' => new CartItemResource($cartItem),
            ], $message);
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Add a book to the cart or return an error if it already exists.
     *
     *
     * @group Customer.Cart
     *
     * @authenticated
     */
    public function addToCart(AddToCartRequest $request): JsonResponse
    {
        $user = AuthUtils::user();
        $cartItemDTO = CartItemDTO::fromRequest($request->validated());

        try {
            $cartItem = $this->cartItemService->addToCart($user, $cartItemDTO);

            return ResponseUtils::created([
                'cart_item' => new CartItemResource($cartItem),
            ], ResponseMessage::ADDED_TO_CART->value);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Sách đã tồn tại trong giỏ hàng.') {
                return ResponseUtils::badRequest(ResponseMessage::ALREADY_IN_CART->value);
            }

            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Remove a book from the cart.
     *
     *
     * @group Customer.Cart
     *
     * @authenticated
     */
    public function removeFromCart(int $book_id): JsonResponse
    {
        $user = AuthUtils::user();

        try {
            $this->cartItemService->removeFromCart($user, $book_id);

            return ResponseUtils::noContent(ResponseMessage::REMOVED_FROM_CART->value);
        } catch (Exception $e) {
            if ($e->getMessage() === 'Sách không tồn tại trong giỏ hàng.') {
                return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_CART_ITEM->value);
            }

            return ResponseUtils::serverError($e->getMessage());
        }
    }
}
