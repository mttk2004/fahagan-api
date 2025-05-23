<?php

namespace App\Services;

use App\DTOs\CartItemDTO;
use App\Models\CartItem;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * Tìm sản phẩm trong giỏ hàng
     */
    public function findCartItem(User $user, int $bookId): ?CartItem
    {
        if (! $user->isBookInCart($bookId)) {
            return null;
        }

        return $user->getCartItemByBook($bookId);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng không cần kiểm tra tồn tại
     *
     * @throws Exception
     * @throws Throwable
     */
    public function addToCartNoChecking(User $user, CartItemDTO $cartItemDTO): CartItem
    {
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
     * Thêm sản phẩm vào giỏ hàng
     *
     * @throws Exception
     * @throws Throwable
     */
    public function addToCart(User $user, CartItemDTO $cartItemDTO): CartItem
    {
        if ($user->isBookInCart($cartItemDTO->book_id)) {
            throw new Exception('Sách đã tồn tại trong giỏ hàng.');
        }

        return $this->addToCartNoChecking($user, $cartItemDTO);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     *
     * @throws Exception
     * @throws Throwable
     */
    public function updateCartItemQuantity(User $user, CartItemDTO $cartItemDTO): CartItem
    {
        if (! $user->isBookInCart($cartItemDTO->book_id)) {
            return $this->addToCartNoChecking($user, $cartItemDTO);
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
     * @throws Throwable
     */
    public function removeFromCart(User $user, int $bookId): bool
    {
        if (! $user->isBookInCart($bookId)) {
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

    /**
     * Xóa toàn bộ giỏ hàng của người dùng
     *
     * @throws Exception
     * @throws Throwable
     */
    public function clearCart(User $user): bool
    {
        try {
            DB::beginTransaction();

            $user->booksInCart()->detach();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
