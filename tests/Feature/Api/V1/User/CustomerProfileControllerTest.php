<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo một người dùng bình thường
        $this->user = User::factory()->create([
          'first_name' => 'Test',
          'last_name' => 'User',
          'email' => 'testuser@example.com',
          'phone' => '0987654321',
          'is_customer' => true,
        ]);
    }

    public function test_it_can_show_user_profile()
    {
        // Gọi API để xem thông tin profile của người dùng đăng nhập
        $response = $this->actingAs($this->user)
          ->getJson('/api/v1/customer/profile');

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'data' => [
              'user',
            ],
          ])
          ->assertJsonPath('data.user.attributes.first_name', 'Test')
          ->assertJsonPath('data.user.attributes.last_name', 'User')
          ->assertJsonPath('data.user.attributes.email', 'testuser@example.com')
          ->assertJsonPath('data.user.attributes.is_customer', true);
    }

    public function test_it_requires_authentication_to_show_profile()
    {
        // Gọi API không có xác thực
        $response = $this->getJson('/api/v1/customer/profile');

        // Kiểm tra response
        $response->assertStatus(401);
    }

    public function test_it_can_update_user_profile()
    {
        // Dữ liệu cập nhật
        $updateData = [
          'first_name' => 'Updated',
          'last_name' => 'Name',
          'phone' => '0987654322',
        ];

        // Gọi API cập nhật thông tin của người dùng đăng nhập
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'user',
            ],
          ])
          ->assertJsonPath('data.user.attributes.first_name', 'Updated')
          ->assertJsonPath('data.user.attributes.last_name', 'Name')
          ->assertJsonPath('data.user.attributes.phone', '0987654322');

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('users', [
          'id' => $this->user->id,
          'first_name' => 'Updated',
          'last_name' => 'Name',
          'phone' => '0987654322',
        ]);
    }

    public function test_it_requires_authentication_to_update_profile()
    {
        // Dữ liệu cập nhật
        $updateData = [
          'first_name' => 'Updated',
        ];

        // Gọi API không có xác thực
        $response = $this->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra response
        $response->assertStatus(401);
    }

    public function test_it_validates_input_when_updating_profile()
    {
        // Dữ liệu cập nhật với email không hợp lệ
        $updateData = [
          'email' => 'invalid-email',
        ];

        // Gọi API cập nhật với dữ liệu không hợp lệ
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra validation errors
        $response->assertStatus(422)
          ->assertJsonValidationErrors(['email']);
    }

    public function test_it_validates_phone_format_when_updating_profile()
    {
        // Dữ liệu cập nhật với số điện thoại không hợp lệ
        $updateData = [
          'phone' => '123456789', // Thiếu số 0 ở đầu
        ];

        // Gọi API cập nhật với dữ liệu không hợp lệ
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra validation errors
        $response->assertStatus(422)
          ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_validates_email_uniqueness_when_updating_profile()
    {
        // Tạo người dùng khác với email cụ thể
        $otherUser = User::factory()->create([
          'email' => 'existing@example.com',
        ]);

        // Dữ liệu cập nhật với email đã tồn tại
        $updateData = [
          'email' => 'existing@example.com',
        ];

        // Gọi API cập nhật
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra validation errors
        $response->assertStatus(422)
          ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_empty_update_data()
    {
        // Dữ liệu cập nhật rỗng
        $updateData = [];

        // Gọi API cập nhật
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra báo lỗi dữ liệu rỗng
        $response->assertStatus(400)
          ->assertJson([
            'status' => 400,
            'message' => 'Không có dữ liệu nào để cập nhật.',
          ]);
    }

    public function test_it_can_update_partial_user_data()
    {
        // Dữ liệu cập nhật chỉ có một trường
        $updateData = [
          'first_name' => 'OnlyFirstName',
        ];

        // Gọi API cập nhật
        $response = $this->actingAs($this->user)
          ->patchJson('/api/v1/customer/profile', $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonPath('data.user.attributes.first_name', 'OnlyFirstName')
          ->assertJsonPath('data.user.attributes.last_name', 'User'); // Trường này không đổi

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('users', [
          'id' => $this->user->id,
          'first_name' => 'OnlyFirstName',
          'last_name' => 'User', // Trường này không đổi
        ]);
    }

    public function test_it_can_delete_user_account()
    {
        // Gọi API xóa tài khoản
        $response = $this->actingAs($this->user)
          ->deleteJson('/api/v1/customer/profile');

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa (soft delete)
        $this->assertSoftDeleted('users', [
          'id' => $this->user->id,
        ]);
    }

    public function test_it_requires_authentication_to_delete_account()
    {
        // Gọi API không có xác thực
        $response = $this->deleteJson('/api/v1/customer/profile');

        // Kiểm tra response
        $response->assertStatus(401);
    }
}
