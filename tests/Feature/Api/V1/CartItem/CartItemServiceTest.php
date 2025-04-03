<?php

namespace Tests\Feature\Api\V1\CartItem;

use App\DTOs\CartItem\CartItemDTO;
use App\Models\Book;
use App\Models\User;
use App\Services\CartItemService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartItemService $cartItemService;
    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartItemService = new CartItemService();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_it_can_get_cart_items()
    {
        // Thêm một vài sản phẩm vào giỏ hàng
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 2]);

        // Tạo thêm một sản phẩm khác
        $anotherBook = Book::factory()->create();
        $this->user->booksInCart()->attach($anotherBook->id, ['quantity' => 1]);

        // Lấy danh sách giỏ hàng
        $cartItems = $this->cartItemService->getCartItems($this->user);

        // Kiểm tra kết quả
        $this->assertCount(2, $cartItems);
        $this->assertEquals($this->book->id, $cartItems[0]->book_id);
        $this->assertEquals(2, $cartItems[0]->quantity);
        $this->assertEquals($anotherBook->id, $cartItems[1]->book_id);
        $this->assertEquals(1, $cartItems[1]->quantity);
    }

    public function test_it_can_add_to_cart()
    {
        // Tạo CartItemDTO để thêm vào giỏ hàng
        $cartItemDTO = new CartItemDTO(
            book_id: $this->book->id,
            quantity: 3
        );

        // Thêm vào giỏ hàng
        $cartItem = $this->cartItemService->addToCart($this->user, $cartItemDTO);

        // Kiểm tra kết quả
        $this->assertEquals($this->book->id, $cartItem->book_id);
        $this->assertEquals(3, $cartItem->quantity);
        $this->assertEquals($this->user->id, $cartItem->user_id);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3
        ]);
    }

    public function test_it_throws_exception_when_adding_existing_book_to_cart()
    {
        // Thêm sách vào giỏ hàng
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Tạo CartItemDTO để thêm lại sách đã có trong giỏ
        $cartItemDTO = new CartItemDTO(
            book_id: $this->book->id,
            quantity: 3
        );

        // Kỳ vọng exception khi thêm lại sách đã có
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Sách đã tồn tại trong giỏ hàng.');

        // Thử thêm lại
        $this->cartItemService->addToCart($this->user, $cartItemDTO);
    }

    public function test_it_can_update_cart_item_quantity()
    {
        // Thêm sách vào giỏ hàng
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Tạo CartItemDTO để cập nhật số lượng
        $cartItemDTO = new CartItemDTO(
            book_id: $this->book->id,
            quantity: 5
        );

        // Cập nhật số lượng
        $cartItem = $this->cartItemService->updateCartItemQuantity($this->user, $cartItemDTO);

        // Kiểm tra kết quả
        $this->assertEquals($this->book->id, $cartItem->book_id);
        $this->assertEquals(5, $cartItem->quantity);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 5
        ]);
    }

    public function test_it_adds_new_item_when_updating_nonexistent_cart_item()
    {
        // Tạo CartItemDTO cho sách chưa có trong giỏ
        $cartItemDTO = new CartItemDTO(
            book_id: $this->book->id,
            quantity: 2
        );

        // Cập nhật (sẽ thêm mới vì chưa có)
        $cartItem = $this->cartItemService->updateCartItemQuantity($this->user, $cartItemDTO);

        // Kiểm tra kết quả
        $this->assertEquals($this->book->id, $cartItem->book_id);
        $this->assertEquals(2, $cartItem->quantity);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 2
        ]);
    }

    public function test_it_can_remove_from_cart()
    {
        // Thêm sách vào giỏ hàng
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Xóa khỏi giỏ hàng
        $result = $this->cartItemService->removeFromCart($this->user, $this->book->id);

        // Kiểm tra kết quả
        $this->assertTrue($result);

        // Kiểm tra dữ liệu đã bị xóa khỏi database
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id
        ]);
    }

    public function test_it_throws_exception_when_removing_nonexistent_cart_item()
    {
        // Kỳ vọng exception khi xóa sách không có trong giỏ hàng
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Sách không tồn tại trong giỏ hàng.');

        // Thử xóa sách không có trong giỏ
        $this->cartItemService->removeFromCart($this->user, $this->book->id);
    }
}
