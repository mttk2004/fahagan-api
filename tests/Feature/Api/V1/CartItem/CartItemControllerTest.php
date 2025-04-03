<?php

namespace Tests\Feature\Api\V1\CartItem;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CartItemControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Authenticatable|User
     */
    private $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo user và book để test
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_it_can_get_cart_items()
    {
        // Thêm một vài sản phẩm vào giỏ hàng
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 2]);

        // Gọi API và kiểm tra response
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('status')
                    ->where('status', 200)
                    ->has('data.cart_items')
                    ->etc();
            });
    }

    public function test_it_can_add_item_to_cart()
    {
        // Dữ liệu để thêm vào giỏ hàng
        $data = [
            'book_id' => $this->book->id,
            'quantity' => 3
        ];

        // Gọi API để thêm vào giỏ hàng
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/add', $data);

        $response->assertStatus(201)
            ->assertJsonPath('status', 201)
            ->assertJsonPath('message', 'Sách đã được thêm vào giỏ hàng.')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'cart_item'
                ]
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3
        ]);
    }

    public function test_it_returns_error_when_adding_existing_item()
    {
        // Thêm sách vào giỏ hàng trước
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Dữ liệu để thêm lại vào giỏ hàng
        $data = [
            'book_id' => $this->book->id,
            'quantity' => 3
        ];

        // Gọi API để thêm lại vào giỏ hàng
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/add', $data);

        $response->assertStatus(400)
            ->assertJsonPath('status', 400)
            ->assertJsonPath('message', 'Sách đã tồn tại trong giỏ hàng.');
    }

    public function test_it_can_update_cart_item_quantity()
    {
        // Thêm sách vào giỏ hàng trước
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Dữ liệu để cập nhật số lượng
        $data = [
            'book_id' => $this->book->id,
            'quantity' => 5
        ];

        // Gọi API để cập nhật số lượng - Sửa thành POST và update-quantity
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/update-quantity', $data);

        $response->assertStatus(200)
            ->assertJsonPath('status', 200)
            ->assertJsonPath('message', 'Số lượng sách trong giỏ hàng đã được cập nhật.')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'cart_item'
                ]
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 5
        ]);
    }

    public function test_it_adds_new_item_when_updating_nonexistent_cart_item()
    {
        // Dữ liệu để cập nhật sách chưa có trong giỏ
        $data = [
            'book_id' => $this->book->id,
            'quantity' => 3
        ];

        // Gọi API để cập nhật - Sửa thành POST và update-quantity
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/update-quantity', $data);

        $response->assertStatus(200);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3
        ]);
    }

    public function test_it_can_remove_item_from_cart()
    {
        // Thêm sách vào giỏ hàng trước
        $this->user->booksInCart()->attach($this->book->id, ['quantity' => 1]);

        // Gọi API để xóa khỏi giỏ hàng
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/cart/remove/{$this->book->id}");

        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa khỏi database
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id
        ]);
    }

    public function test_it_returns_error_when_removing_nonexistent_cart_item()
    {
        // Gọi API để xóa sách không có trong giỏ
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/cart/remove/{$this->book->id}");

        $response->assertStatus(404)
            ->assertJsonPath('status', 404)
            ->assertJsonPath('message', 'Sách không tồn tại trong giỏ hàng.');
    }

    public function test_it_validates_input_for_adding_to_cart()
    {
        // Dữ liệu thiếu thông tin book_id
        $invalidData = [
            'quantity' => 3
        ];

        // Gọi API với dữ liệu không hợp lệ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/add', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['book_id']);

        // Kiểm tra với số lượng không hợp lệ
        $invalidQuantityData = [
            'book_id' => $this->book->id,
            'quantity' => -1
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cart/add', $invalidQuantityData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }
}
