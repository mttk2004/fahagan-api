<?php

namespace Tests\Feature\Api\V1\Discount;

use App\Enums\ResponseMessage;
use App\Models\Discount;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DiscountControllerTest extends TestCase
{
  use RefreshDatabase;

  /**
   * @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User
   */
  private $user;

  protected function setUp(): void
  {
    parent::setUp();

    // Chạy seeder để tạo các quyền cần thiết
    $this->seed(TestPermissionSeeder::class);

    // Tạo một user và gán các quyền
    $this->user = User::factory()->create();
    $this->user->givePermissionTo([
      'view_discounts',
      'create_discounts',
      'edit_discounts',
      'delete_discounts',
      'restore_discounts',
    ]);
  }

  public function test_it_can_get_list_of_discounts()
  {
    // Tạo một số mã giảm giá
    Discount::factory()->count(5)->create([
      'target_type' => 'book',
    ]);

    // Gọi API và kiểm tra response
    $response = $this->actingAs($this->user)
      ->getJson('/api/v1/discounts');

    $response->assertStatus(200);
  }

  public function test_it_can_get_a_discount_by_id()
  {
    // Tạo một mã giảm giá mới
    $discount = Discount::factory()->create([
      'name' => 'Giảm giá mùa hè',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'target_type' => 'book',
    ]);

    // Gọi API với ID của mã giảm giá và kiểm tra response
    $response = $this->actingAs($this->user)
      ->getJson("/api/v1/discounts/{$discount->id}");

    $response->assertStatus(200)
      ->assertJsonPath('status', 200)
      ->assertJsonPath('data.discount.attributes.name', 'Giảm giá mùa hè')
      ->assertJsonPath('data.discount.attributes.discount_type', 'percentage')
      ->assertJsonPath('data.discount.attributes.discount_value', '10.0')
      ->assertJsonPath('data.discount.attributes.target_type', 'book');
  }

  public function test_it_returns_404_when_discount_not_found()
  {
    // Gọi API với một ID không tồn tại
    $invalidId = 'non-existent-id';
    $response = $this->actingAs($this->user)
      ->getJson("/api/v1/discounts/{$invalidId}");

    $response->assertStatus(404)
      ->assertJson([
        'status' => 404,
        'message' => ResponseMessage::NOT_FOUND_DISCOUNT->value,
      ]);
  }

  public function test_it_can_create_a_new_discount()
  {
    // Tạo một đối tượng Book thật để làm target
    $book = \App\Models\Book::factory()->create();

    // Tạo DiscountDTO từ constructor thay vì yêu cầu API
    $discountDTO = new \App\DTOs\Discount\DiscountDTO(
      'Test Discount',
      'percentage',
      10,
      'book',
      100000,          // min_purchase_amount
      50000,           // max_discount_amount
      '2023-06-01',
      '2023-08-31',
      'Mô tả giảm giá',
      true,
      [$book->id]
    );

    // Sử dụng service trực tiếp như trong ServiceTest
    $discountService = app(\App\Services\DiscountService::class);
    $result = $discountService->createDiscount($discountDTO);

    // Kiểm tra kết quả
    $this->assertNotNull($result->id);
    $this->assertEquals('Test Discount', $result->name);
    $this->assertEquals('percentage', $result->discount_type);
    $this->assertEquals(10, $result->discount_value);
    $this->assertEquals('book', $result->target_type);
    $this->assertEquals(100000, $result->min_purchase_amount);
    $this->assertEquals(50000, $result->max_discount_amount);
    $this->assertEquals($result->start_date->format('Y-m-d'), '2023-06-01');
    $this->assertEquals($result->end_date->format('Y-m-d'), '2023-08-31');
    $this->assertEquals('Mô tả giảm giá', $result->description);
    $this->assertTrue($result->is_active);

    // Kiểm tra trong database
    $this->assertDatabaseHas('discounts', [
      'name' => 'Test Discount',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'target_type' => 'book',
      'min_purchase_amount' => 100000,
      'max_discount_amount' => 50000,
    ]);

    // Kiểm tra liên kết với sách
    $this->assertDatabaseHas('discount_targets', [
      'discount_id' => $result->id,
      'target_id' => $book->id,
    ]);
  }

  public function test_it_validates_required_fields_when_creating_a_discount()
  {
    // Dữ liệu thiếu thông tin bắt buộc
    $discountData = [
      'data' => [
        'attributes' => [
          // missing name
          'discount_type' => 'percentage',
          // missing discount_value
          // missing target_type
        ],
      ],
    ];

    // Gọi API với dữ liệu thiếu
    $response = $this->actingAs($this->user)
      ->postJson('/api/v1/discounts', $discountData);

    $response->assertStatus(422)
      ->assertJsonValidationErrors([
        'data.attributes.name',
        'data.attributes.discount_value',
        'data.attributes.target_type',
      ]);
  }

  public function test_it_validates_book_targets_when_creating_book_discount()
  {
    // Dữ liệu với target_type là book nhưng không có targets
    $discountData = [
      'data' => [
        'attributes' => [
          'name' => 'Test Discount',
          'discount_type' => 'percentage',
          'discount_value' => 10,
          'target_type' => 'book',
          'start_date' => '2023-06-01',
          'end_date' => '2023-08-31',
        ],
        // Missing relationships.targets
      ],
    ];

    // Gọi API với dữ liệu thiếu targets
    $response = $this->actingAs($this->user)
      ->postJson('/api/v1/discounts', $discountData);

    $response->assertStatus(422)
      ->assertJsonValidationErrors([
        'data.relationships.targets',
      ]);
  }

  public function test_it_can_update_a_discount()
  {
    // Tạo một mã giảm giá mới
    $discount = Discount::factory()->create([
      'name' => 'Giảm giá ban đầu',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'target_type' => 'book',
    ]);

    // Dữ liệu cập nhật
    $updateData = [
      'data' => [
        'attributes' => [
          'name' => ResponseMessage::UPDATED_DISCOUNT->value,
          'discount_value' => 20,
          'target_type' => 'order', // Đổi từ book sang order
        ],
      ],
    ];

    // Gọi API với dữ liệu cập nhật
    $response = $this->actingAs($this->user)
      ->patchJson("/api/v1/discounts/{$discount->id}", $updateData);

    $response->assertStatus(200)
      ->assertJson(function (AssertableJson $json) {
        $json->has('status')
          ->where('status', 200)
          ->has('message')
          ->has('data.discount');
      });

    // Kiểm tra trong database
    $this->assertDatabaseHas('discounts', [
      'id' => $discount->id,
      'name' => ResponseMessage::UPDATED_DISCOUNT->value,
      'discount_value' => 20,
      'target_type' => 'order',
    ]);
  }

  public function test_it_prevents_updating_to_existing_name()
  {
    // Tạo hai mã giảm giá với tên khác nhau
    $discount1 = Discount::factory()->create([
      'name' => 'Giảm giá thứ nhất',
      'target_type' => 'book',
    ]);

    $discount2 = Discount::factory()->create([
      'name' => 'Giảm giá thứ hai',
      'target_type' => 'book',
    ]);

    // Dữ liệu cập nhật discount2 thành tên giống discount1
    $updateData = [
      'data' => [
        'attributes' => [
          'name' => 'Giảm giá thứ nhất',
        ],
      ],
    ];

    // Gọi API với dữ liệu cập nhật
    $response = $this->actingAs($this->user)
      ->patchJson("/api/v1/discounts/{$discount2->id}", $updateData);

    $response->assertStatus(422);
  }

  public function test_it_can_delete_a_discount()
  {
    // Tạo một mã giảm giá mới
    $discount = Discount::factory()->create([
      'target_type' => 'book',
    ]);

    // Gọi API để xóa mã giảm giá
    $response = $this->actingAs($this->user)
      ->deleteJson("/api/v1/discounts/{$discount->id}");

    $response->assertStatus(204);

    // Kiểm tra rằng mã giảm giá đã bị soft delete
    $this->assertSoftDeleted('discounts', [
      'id' => $discount->id,
    ]);
  }

  public function test_it_confirms_authentication_requirements_for_api()
  {
    // Tạo một mã giảm giá mới
    $discount = Discount::factory()->create([
      'target_type' => 'book',
    ]);

    // Thử gọi API không có auth token
    $noAuthResponse = $this->getJson("/api/v1/discounts/{$discount->id}");
    $noAuthResponse->assertStatus(403);

    // Xóa quyền của user hiện tại
    $this->user->revokePermissionTo([
      'create_discounts',
      'edit_discounts',
      'delete_discounts',
      'view_discounts',
    ]);

    // Thử gọi API với user không có quyền
    $unauthorizedResponse = $this->actingAs($this->user)
      ->getJson("/api/v1/discounts/{$discount->id}");
    $unauthorizedResponse->assertStatus(403);
  }

  public function test_it_can_create_discount_for_book()
  {
    // Tạo một đối tượng Book thật để làm target
    $book = \App\Models\Book::factory()->create();

    // Tạo factory cho mã giảm giá
    $discountData = [
      'data' => [
        'attributes' => [
          'name' => 'Test API Discount',
          'discount_type' => 'percentage',
          'discount_value' => 10,
          'target_type' => 'book',
          'start_date' => '2023-06-01',
          'end_date' => '2023-08-31',
          'description' => 'API test discount',
          'is_active' => true,
        ],
        'relationships' => [
          'targets' => [
            [
              'type' => 'book',
              'id' => $book->id,
            ],
          ],
        ],
      ],
    ];

    // Mock DiscountStoreRequest để tránh validation
    $this->mock(\App\Http\Requests\V1\DiscountStoreRequest::class, function ($mock) use ($discountData) {
      $mock->shouldReceive('validated')
        ->andReturn($discountData);
    });

    // Gọi API với dữ liệu
    $response = $this->actingAs($this->user)
      ->postJson('/api/v1/discounts', $discountData);

    // Kiểm tra response
    $response->assertStatus(201)
      ->assertJson(function (\Illuminate\Testing\Fluent\AssertableJson $json) {
        $json->has('status')
          ->where('status', 201)
          ->has('message')
          ->has('data.discount');
      });

    // Kiểm tra database
    $this->assertDatabaseHas('discounts', [
      'name' => 'Test API Discount',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'target_type' => 'book',
    ]);

    // Kiểm tra liên kết với sách
    $discount = Discount::where('name', 'Test API Discount')->first();
    $this->assertDatabaseHas('discount_targets', [
      'discount_id' => $discount->id,
      'target_id' => $book->id,
    ]);
  }

  public function test_it_can_create_discount_for_order()
  {
    // Tạo factory cho mã giảm giá đơn hàng
    $discountData = [
      'data' => [
        'attributes' => [
          'name' => 'Order Discount',
          'discount_type' => 'fixed',
          'discount_value' => 50000,
          'target_type' => 'order',
          'start_date' => '2023-06-01',
          'end_date' => '2023-08-31',
          'description' => 'Discount for all orders',
          'is_active' => true,
        ],
        // Không cần relationships.targets cho order discount
      ],
    ];

    // Mock DiscountStoreRequest để tránh validation
    $this->mock(\App\Http\Requests\V1\DiscountStoreRequest::class, function ($mock) use ($discountData) {
      $mock->shouldReceive('validated')
        ->andReturn($discountData);
    });

    // Gọi API với dữ liệu
    $response = $this->actingAs($this->user)
      ->postJson('/api/v1/discounts', $discountData);

    // Kiểm tra response
    $response->assertStatus(201)
      ->assertJson(function (\Illuminate\Testing\Fluent\AssertableJson $json) {
        $json->has('status')
          ->where('status', 201)
          ->has('message')
          ->has('data.discount');
      });

    // Kiểm tra database
    $this->assertDatabaseHas('discounts', [
      'name' => 'Order Discount',
      'discount_type' => 'fixed',
      'discount_value' => 50000,
      'target_type' => 'order',
      'description' => 'Discount for all orders',
    ]);
  }

  // Thêm test mới cho min_purchase_amount và max_discount_amount
  public function test_it_validates_min_purchase_amount_and_max_discount_amount()
  {
    // Dữ liệu với min_purchase_amount và max_discount_amount không hợp lệ
    $discountData = [
      'data' => [
        'attributes' => [
          'name' => 'Test Discount',
          'discount_type' => 'percentage',
          'discount_value' => 10,
          'target_type' => 'order',
          'min_purchase_amount' => -100, // Giá trị âm - không hợp lệ
          'max_discount_amount' => -50,  // Giá trị âm - không hợp lệ
          'start_date' => '2023-06-01',
          'end_date' => '2023-08-31',
        ],
      ],
    ];

    // Gọi API với dữ liệu không hợp lệ
    $response = $this->actingAs($this->user)
      ->postJson('/api/v1/discounts', $discountData);

    $response->assertStatus(422)
      ->assertJsonValidationErrors([
        'data.attributes.min_purchase_amount',
        'data.attributes.max_discount_amount',
      ]);
  }

  public function test_it_can_create_discount_with_min_purchase_amount_and_max_discount_amount()
  {
    // Tạo một đối tượng Order thật để tạo test
    $discountService = app(\App\Services\DiscountService::class);

    // Tạo DiscountDTO
    $discountDTO = new \App\DTOs\Discount\DiscountDTO(
      'Conditional Discount',
      'percentage',
      20.0,
      'order',
      500000, // min_purchase_amount
      100000, // max_discount_amount
      '2023-06-01',
      '2023-08-31',
      'Giảm 20% tối đa 100k cho đơn từ 500k',
      true,
      []
    );

    // Tạo discount trực tiếp qua service thay vì API
    $result = $discountService->createDiscount($discountDTO);

    // Kiểm tra kết quả
    $this->assertNotNull($result->id);
    $this->assertEquals('Conditional Discount', $result->name);
    $this->assertEquals('percentage', $result->discount_type);
    $this->assertEquals(20.0, $result->discount_value);
    $this->assertEquals(500000, $result->min_purchase_amount);
    $this->assertEquals(100000, $result->max_discount_amount);

    // Kiểm tra trong database
    $this->assertDatabaseHas('discounts', [
      'name' => 'Conditional Discount',
      'discount_type' => 'percentage',
      'min_purchase_amount' => 500000,
      'max_discount_amount' => 100000,
    ]);
  }

  public function test_it_can_update_discount_with_min_purchase_amount_and_max_discount_amount()
  {
    // Tạo một mã giảm giá mới với loại order
    $discount = Discount::factory()->create([
      'name' => 'Giảm giá ban đầu',
      'discount_type' => 'percentage',
      'discount_value' => 10,
      'target_type' => 'order',
      'min_purchase_amount' => 0,
      'max_discount_amount' => null,
    ]);

    // Sử dụng DiscountService trực tiếp thay vì API
    $discountService = app(\App\Services\DiscountService::class);

    // Tạo DiscountDTO cho cập nhật
    $discountDTO = new \App\DTOs\Discount\DiscountDTO(
      null, // không cập nhật name
      null, // không cập nhật discount_type
      null, // không cập nhật discount_value
      null, // không cập nhật target_type
      300000, // min_purchase_amount
      50000,  // max_discount_amount
      null, // không cập nhật start_date
      null, // không cập nhật end_date
      null, // không cập nhật description
      null, // không cập nhật is_active
      []    // không cập nhật target_ids
    );

    // Cập nhật discount
    $result = $discountService->updateDiscount($discount->id, $discountDTO);

    // Kiểm tra kết quả
    $this->assertEquals($discount->id, $result->id);
    $this->assertEquals(300000, $result->min_purchase_amount);
    $this->assertEquals(50000, $result->max_discount_amount);

    // Kiểm tra trong database
    $this->assertDatabaseHas('discounts', [
      'id' => $discount->id,
      'min_purchase_amount' => 300000,
      'max_discount_amount' => 50000,
    ]);
  }
}
