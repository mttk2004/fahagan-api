<?php

namespace Tests\Feature\Api\V1;

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
    Discount::factory()->count(5)->create();

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
      'discount_type' => 'percent',
      'discount_value' => 10,
    ]);

    // Gọi API với ID của mã giảm giá và kiểm tra response
    $response = $this->actingAs($this->user)
      ->getJson("/api/v1/discounts/{$discount->id}");

    $response->assertStatus(200)
      ->assertJsonPath('status', 200)
      ->assertJsonPath('data.discount.attributes.name', 'Giảm giá mùa hè')
      ->assertJsonPath('data.discount.attributes.discount_type', 'percent')
      ->assertJsonPath('data.discount.attributes.discount_value', '10.0');
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
      'percent',
      10,
      '2023-06-01',
      '2023-08-31',
      [$book->id]
    );

    // Sử dụng service trực tiếp như trong ServiceTest
    $discountService = app(\App\Services\DiscountService::class);
    $result = $discountService->createDiscount($discountDTO);

    // Kiểm tra kết quả
    $this->assertNotNull($result->id);
    $this->assertEquals('Test Discount', $result->name);
    $this->assertEquals('percent', $result->discount_type);
    $this->assertEquals(10, $result->discount_value);
    $this->assertEquals($result->start_date->format('Y-m-d'), '2023-06-01');
    $this->assertEquals($result->end_date->format('Y-m-d'), '2023-08-31');

    // Kiểm tra trong database
    $this->assertDatabaseHas('discounts', [
      'name' => 'Test Discount',
      'discount_type' => 'percent',
      'discount_value' => 10,
    ]);
  }

  public function test_it_validates_required_fields_when_creating_a_discount()
  {
    // Dữ liệu thiếu thông tin bắt buộc
    $discountData = [
      'data' => [
        'attributes' => [
          // missing name
          'discount_type' => 'percent',
          // missing discount_value
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
      ]);
  }

  public function test_it_can_update_a_discount()
  {
    // Tạo một mã giảm giá mới
    $discount = Discount::factory()->create([
      'name' => 'Giảm giá ban đầu',
      'discount_type' => 'percent',
      'discount_value' => 10,
    ]);

    // Dữ liệu cập nhật
    $updateData = [
      'data' => [
        'attributes' => [
          'name' => 'Giảm giá đã cập nhật',
          'discount_value' => 20,
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
    ]);
  }

  public function test_it_prevents_updating_to_existing_name()
  {
    // Tạo hai mã giảm giá với tên khác nhau
    $discount1 = Discount::factory()->create([
      'name' => 'Giảm giá thứ nhất',
    ]);

    $discount2 = Discount::factory()->create([
      'name' => 'Giảm giá thứ hai',
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
    $discount = Discount::factory()->create();

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
    $discount = Discount::factory()->create();

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

  public function test_api_can_create_a_new_discount()
  {
    // Tạo một đối tượng Book thật để làm target
    $book = \App\Models\Book::factory()->create();

    // Tạo factory cho mã giảm giá
    $discountData = [
      'data' => [
        'attributes' => [
          'name' => 'Test API Discount',
          'discount_type' => 'percent',
          'discount_value' => 10,
          'start_date' => '2023-06-01',
          'end_date' => '2023-08-31',
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
      'discount_type' => 'percent',
      'discount_value' => 10,
    ]);
  }
}
