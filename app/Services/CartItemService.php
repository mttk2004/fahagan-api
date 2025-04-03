<?php

namespace App\Services;

use App\DTOs\CartItem\CartItemDTO;
use App\Models\CartItem;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CartItemService
{
    /**
     * Lấy tất cả các sản phẩm trong giỏ hàng của người dùng
     */
    public function getCartItems(User $user): Collection
    {
        return $user->cartItems()
                    ->with('book')
                    ->get();
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     *
     * @throws Exception
     */
    public function addToCart(User $user, CartItemDTO $cartItemDTO): CartItem
    {
        if ($user->isBookInCart($cartItemDTO->book_id)) {
            throw new Exception('Sách đã tồn tại trong giỏ hàng.');
        }

        try {
            DB::beginTransaction();

            $user->booksInCart()->attach($cartItemDTO->book_id, [
                'quantity' => $cartItemDTO->quantity,
            ]);

            $cartItem = $user->getCartItemByBook($cartItemDTO->book_id);

            DB::commit();

            return $cartItem;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     *
     * @throws Exception
     */
    public function updateCartItemQuantity(User $user, CartItemDTO $cartItemDTO): CartItem
    {
        if (!$user->isBookInCart($cartItemDTO->book_id)) {
            return $this->addToCart($user, $cartItemDTO);
        }

        try {
            DB::beginTransaction();

            $user->booksInCart()->updateExistingPivot($cartItemDTO->book_id, [
                'quantity' => $cartItemDTO->quantity,
            ]);

            $cartItem = $user->getCartItemByBook($cartItemDTO->book_id);

            DB::commit();

            return $cartItem;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     *
     * @throws Exception
     */
    public function removeFromCart(User $user, int $bookId): bool
    {
        if (!$user->isBookInCart($bookId)) {
            throw new Exception('Sách không tồn tại trong giỏ hàng.');
        }

        try {
            DB::beginTransaction();

            $user->booksInCart()->detach($bookId);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
