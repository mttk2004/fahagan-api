<?php

namespace Tests\Feature\Api\V1\Address;

use App\DTOs\Address\AddressDTO;
use App\Models\Address;
use App\Models\User;
use App\Services\AddressService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
    use RefreshDatabase;

    private AddressService $addressService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addressService = new AddressService();
        $this->user = User::factory()->create();
    }

    public function test_it_can_get_all_addresses()
    {
        // Tạo một số địa chỉ cho người dùng
        Address::factory(3)->create(['user_id' => $this->user->id]);

        // Gọi service để lấy tất cả địa chỉ
        $result = $this->addressService->getAllAddresses($this->user, new Request());

        // Kiểm tra kết quả
        $this->assertEquals(3, $result->total());
    }

    public function test_it_can_create_address()
    {
        // Tạo DTO
        $addressDTO = new AddressDTO(
            name: 'Nguyễn Văn A',
            phone: '0123456789',
            city: 'Hà Nội',
            district: 'Cầu Giấy',
            ward: 'Dịch Vọng',
            address_line: 'Số 1 Đường ABC'
        );

        // Gọi service để tạo địa chỉ
        $result = $this->addressService->createAddress($this->user, $addressDTO);

        // Kiểm tra kết quả
        $this->assertDatabaseHas('addresses', [
            'id' => $result->id,
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC'
        ]);
    }

    public function test_it_can_get_address_by_id()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        // Gọi service để lấy thông tin địa chỉ
        $result = $this->addressService->getAddressById($this->user, $address->id);

        // Kiểm tra kết quả
        $this->assertEquals($address->id, $result->id);
        $this->assertEquals($address->name, $result->name);
    }

    public function test_it_throws_exception_when_address_not_found()
    {
        // Kỳ vọng exception được ném ra khi không tìm thấy địa chỉ
        $this->expectException(ModelNotFoundException::class);

        // Gọi service với ID không tồn tại
        $this->addressService->getAddressById($this->user, 999999);
    }

    public function test_it_can_update_address()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'city' => 'Hà Nội'
        ]);

        // Tạo DTO cập nhật
        $addressDTO = new AddressDTO(
            name: 'Nguyễn Văn B',
            phone: '9876543210'
        );

        // Gọi service để cập nhật địa chỉ
        $result = $this->addressService->updateAddress($this->user, $address->id, $addressDTO);

        // Kiểm tra kết quả
        $this->assertEquals('Nguyễn Văn B', $result->name);
        $this->assertEquals('9876543210', $result->phone);
        $this->assertEquals('Hà Nội', $result->city); // Giữ nguyên giá trị cũ
    }

    public function test_it_can_update_partial_address_info()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0123456789',
            'city' => 'Hà Nội'
        ]);

        // Tạo DTO chỉ cập nhật thành phố
        $addressDTO = new AddressDTO(
            city: 'Hồ Chí Minh'
        );

        // Gọi service để cập nhật một phần thông tin
        $result = $this->addressService->updateAddress($this->user, $address->id, $addressDTO);

        // Kiểm tra kết quả
        $this->assertEquals('Nguyễn Văn A', $result->name); // Không thay đổi
        $this->assertEquals('0123456789', $result->phone); // Không thay đổi
        $this->assertEquals('Hồ Chí Minh', $result->city); // Đã thay đổi
    }

    public function test_it_can_delete_address()
    {
        // Tạo một địa chỉ
        $address = Address::factory()->create(['user_id' => $this->user->id]);

        // Gọi service để xóa địa chỉ
        $this->addressService->deleteAddress($this->user, $address->id);

        // Kiểm tra địa chỉ đã bị xóa
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id
        ]);
    }

    public function test_it_throws_exception_when_deleting_other_user_address()
    {
        // Tạo một người dùng khác
        $anotherUser = User::factory()->create();

        // Tạo một địa chỉ cho người dùng khác
        $address = Address::factory()->create(['user_id' => $anotherUser->id]);

        // Kỳ vọng exception được ném ra khi xóa địa chỉ của người dùng khác
        $this->expectException(ModelNotFoundException::class);

        // Gọi service để xóa địa chỉ của người dùng khác
        $this->addressService->deleteAddress($this->user, $address->id);
    }
}
