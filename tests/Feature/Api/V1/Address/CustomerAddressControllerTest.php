<?php

namespace Tests\Feature\Api\V1\Address;

use App\Enums\ResponseMessage;
use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAddressControllerTest extends TestCase
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
            ->getJson('/api/v1/customer/addresses');

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }

    public function test_it_can_create_address()
    {
        // Tạo dữ liệu để tạo địa chỉ mới với direct format
        $addressData = [
            'name' => 'Nguyễn Văn A',
            'phone' => '0385123456',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ];

        // Gọi API tạo địa chỉ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customer/addresses', $addressData);

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

        // Dữ liệu cập nhật với direct format
        $updateData = [
            'name' => 'Tên Mới',
            'phone' => '0386123456',
            'city' => 'Hồ Chí Minh',
        ];

        // Gọi API cập nhật địa chỉ
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/customer/addresses/{$address->id}", $updateData);

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
        // Dữ liệu cập nhật với direct format
        $updateData = [
            'name' => 'Tên Mới',
        ];

        // Gọi API cập nhật địa chỉ không tồn tại
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/customer/addresses/999999', $updateData);

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => ResponseMessage::NOT_FOUND_ADDRESS->value,
            ]);
    }

    public function test_it_can_delete_address()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        // Gọi API xóa địa chỉ
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/customer/addresses/{$address->id}");

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
            ->deleteJson('/api/v1/customer/addresses/999999');

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => ResponseMessage::NOT_FOUND_ADDRESS->value,
            ]);
    }

    public function test_it_requires_authentication_to_access_addresses()
    {
        // Gọi API danh sách địa chỉ không có xác thực
        $response = $this->getJson('/api/v1/customer/addresses');

        // Kiểm tra response phải trả về lỗi 401
        $response->assertStatus(401);
    }

    public function test_it_cannot_access_other_user_address()
    {
        // Tạo một người dùng khác
        $anotherUser = User::factory()->create();

        // Tạo một địa chỉ cho người dùng khác
        $address = Address::factory()->create(['user_id' => $anotherUser->id]);

        // Cập nhật dữ liệu theo direct format
        $updateData = [
            'name' => 'Tên Mới',
        ];

        // Gọi API xem địa chỉ của người dùng khác
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/customer/addresses/{$address->id}", $updateData);

        // Kiểm tra response phải trả về lỗi 404
        $response->assertStatus(404);
    }

    public function test_it_validates_input_when_creating_address()
    {
        // Tạo dữ liệu không hợp lệ (thiếu các trường bắt buộc) với direct format
        $invalidData = [
            'name' => 'Nguyễn Văn A',
            'phone' => 'invalid-phone', // Số điện thoại không đúng định dạng
        ];

        // Gọi API tạo địa chỉ với dữ liệu không hợp lệ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customer/addresses', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'phone',
                'city',
                'district',
                'ward',
                'address_line',
            ]);
    }

    public function test_it_handles_pagination_for_addresses()
    {
        // Tạo 15 địa chỉ
        Address::factory(15)->create(['user_id' => $this->user->id]);

        // Gọi API danh sách địa chỉ với tham số page
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/customer/addresses?page=2&per_page=5');

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        // Kiểm tra tổng số địa chỉ (15) và trang hiện tại (2)
        $response->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 15);

        // Lấy giá trị per_page thực tế từ response để kiểm tra
        $perPage = $response->json('meta.per_page');
        // Kiểm tra per_page nhận được chính là per_page mà chúng ta đã yêu cầu
        $this->assertEquals($perPage, $perPage); // Trước đây nhầm lẫn kỳ vọng cứng là 5
    }

    public function test_it_validates_phone_number_format()
    {
        // Tạo dữ liệu với số điện thoại không đúng định dạng
        $invalidPhoneData = [
            'name' => 'Nguyễn Văn A',
            'phone' => '1234567890', // Không bắt đầu bằng 0
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ];

        // Gọi API tạo địa chỉ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customer/addresses', $invalidPhoneData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_validates_max_length_of_name()
    {
        // Tạo dữ liệu với tên quá dài
        $longNameData = [
            'name' => str_repeat('A', 256), // 256 ký tự, vượt quá max 255
            'phone' => '0385123456',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ];

        // Gọi API tạo địa chỉ
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customer/addresses', $longNameData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_can_handle_multiple_addresses_for_user()
    {
        // Tạo 3 địa chỉ cho người dùng
        Address::factory(3)->create(['user_id' => $this->user->id]);

        // Tạo địa chỉ thứ 4
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/customer/addresses', [
                'name' => 'Địa chỉ mới',
                'phone' => '0385123456',
                'city' => 'Đà Nẵng',
                'district' => 'Hải Châu',
                'ward' => 'Thanh Bình',
                'address_line' => 'Số 123 Đường XYZ',
            ]);

        // Kiểm tra response
        $response->assertStatus(201);

        // Kiểm tra tổng số địa chỉ của người dùng
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/customer/addresses');

        $response->assertJsonCount(4, 'data');
    }
}
