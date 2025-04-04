<?php

namespace Tests\Feature\Api\V1\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo một người dùng bình thường
        $this->user = User::factory()->create([
            'is_customer' => true,
        ]);
    }

    public function test_it_can_get_list_of_addresses()
    {
        // Tạo 3 địa chỉ
        Address::factory(3)->create(['user_id' => $this->user->id]);

        // Gọi API danh sách địa chỉ
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/addresses');

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
            ]);
    }

    public function test_it_can_create_address()
    {
        // Tạo dữ liệu để tạo địa chỉ mới với cấu trúc JSON:API
        $addressData = [
            'data' => [
                'attributes' => [
                    'name' => 'Nguyễn Văn A',
                    'phone' => '0385123456',
                    'city' => 'Hà Nội',
                    'district' => 'Cầu Giấy',
                    'ward' => 'Dịch Vọng',
                    'address_line' => 'Số 1 Đường ABC',
                ],
            ],
        ];

        // Gọi API tạo địa chỉ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/addresses', $addressData);

        // Kiểm tra response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'address',
                ],
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0385123456',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ]);
    }

    public function test_it_can_update_address()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        // Dữ liệu cập nhật với cấu trúc JSON:API
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Tên Mới',
                    'phone' => '0386123456',
                    'city' => 'Hồ Chí Minh',
                ],
            ],
        ];

        // Gọi API cập nhật địa chỉ
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/addresses/{$address->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'address',
                ],
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $this->user->id,
            'name' => 'Tên Mới',
            'phone' => '0386123456',
            'city' => 'Hồ Chí Minh',
        ]);
    }

    public function test_it_returns_404_when_updating_nonexistent_address()
    {
        // Dữ liệu cập nhật với định dạng JSON:API
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Tên Mới',
                ],
            ],
        ];

        // Gọi API cập nhật địa chỉ không tồn tại
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/addresses/999999", $updateData);

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Không tìm thấy địa chỉ.',
            ]);
    }

    public function test_it_can_delete_address()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        // Gọi API xóa địa chỉ
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/addresses/{$address->id}");

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_it_returns_404_when_deleting_nonexistent_address()
    {
        // Gọi API xóa địa chỉ không tồn tại
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/addresses/999999");

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Không tìm thấy địa chỉ.',
            ]);
    }

    public function test_it_requires_authentication_to_access_addresses()
    {
        // Gọi API danh sách địa chỉ không có xác thực
        $response = $this->getJson('/api/v1/addresses');

        // Kiểm tra response phải trả về lỗi 401
        $response->assertStatus(401);
    }

    public function test_it_cannot_access_other_user_address()
    {
        // Tạo một người dùng khác
        $anotherUser = User::factory()->create();

        // Tạo một địa chỉ cho người dùng khác
        $address = Address::factory()->create(['user_id' => $anotherUser->id]);

        // Cập nhật dữ liệu theo định dạng JSON:API
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Tên Mới',
                ],
            ],
        ];

        // Gọi API xem địa chỉ của người dùng khác
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/addresses/{$address->id}", $updateData);

        // Kiểm tra response phải trả về lỗi 404
        $response->assertStatus(404);
    }

    public function test_it_validates_input_when_creating_address()
    {
        // Tạo dữ liệu không hợp lệ (thiếu các trường bắt buộc) với cấu trúc JSON:API
        $invalidData = [
            'data' => [
                'attributes' => [
                    'name' => 'Nguyễn Văn A',
                    'phone' => 'invalid-phone', // Số điện thoại không đúng định dạng
                ],
            ],
        ];

        // Gọi API tạo địa chỉ với dữ liệu không hợp lệ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/addresses', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.phone',
                'data.attributes.city',
                'data.attributes.district',
                'data.attributes.ward',
                'data.attributes.address_line',
            ]);
    }
}
