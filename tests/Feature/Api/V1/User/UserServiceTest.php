<?php

namespace Tests\Feature\Api\V1\User;

use App\DTOs\UserDTO;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new CustomerService;
    }

    public function test_it_can_get_all_users()
    {
        // Tạo 5 người dùng
        User::factory()->count(5)->create();

        // Tạo request rỗng
        $request = new Request;

        // Lấy danh sách người dùng
        $users = $this->userService->getAllUsers($request);

        // Kiểm tra số lượng người dùng
        $this->assertEquals(5, $users->count());
    }

    public function test_it_can_filter_users_by_name()
    {
        // Tạo 3 người dùng có họ tên khác nhau
        User::factory()->create(['first_name' => 'Nguyễn', 'last_name' => 'Văn A']);
        User::factory()->create(['first_name' => 'Trần', 'last_name' => 'Văn B']);
        User::factory()->create(['first_name' => 'Lê', 'last_name' => 'Văn C']);

        // Tạo request với filter theo tên
        $request = new Request(['filter' => ['name' => 'Nguyễn']]);

        // Lấy danh sách người dùng
        $users = $this->userService->getAllUsers($request);

        // Kiểm tra số lượng người dùng
        $this->assertEquals(1, $users->count());
        $this->assertEquals('Nguyễn', $users->first()->first_name);
    }

    public function test_it_can_filter_users_by_is_customer()
    {
        // Tạo 3 người dùng, 2 là khách hàng, 1 là nhân viên
        User::factory()->count(2)->create(['is_customer' => true]);
        User::factory()->create(['is_customer' => false]);

        // Tạo request với filter theo is_customer
        $request = new Request(['filter' => ['is_customer' => 'false']]);

        // Lấy danh sách người dùng
        $users = $this->userService->getAllUsers($request);

        // Kiểm tra số lượng người dùng
        $this->assertEquals(1, $users->count());
        $this->assertFalse($users->first()->is_customer);
    }

    public function test_it_can_sort_users()
    {
        // Tạo 3 người dùng
        User::factory()->create(['first_name' => 'A', 'last_name' => 'Nguyễn']);
        User::factory()->create(['first_name' => 'B', 'last_name' => 'Trần']);
        User::factory()->create(['first_name' => 'C', 'last_name' => 'Lê']);

        // Tạo request với sort theo first_name giảm dần
        $request = new Request(['sort' => '-first_name']);

        // Lấy danh sách người dùng
        $users = $this->userService->getAllUsers($request);

        // Kiểm tra thứ tự sắp xếp
        $this->assertEquals('C', $users[0]->first_name);
        $this->assertEquals('B', $users[1]->first_name);
        $this->assertEquals('A', $users[2]->first_name);
    }

    public function test_it_can_create_user()
    {
        // Tạo UserDTO
        $userDTO = new UserDTO(
            first_name: 'Nguyễn',
            last_name: 'Văn A',
            email: 'nguyenvana@example.com',
            phone: '0123456789',
            password: bcrypt('password'),
            is_customer: true
        );

        // Tạo người dùng mới
        $user = $this->userService->createUser($userDTO);

        // Kiểm tra thông tin người dùng
        $this->assertEquals('Nguyễn', $user->first_name);
        $this->assertEquals('Văn A', $user->last_name);
        $this->assertEquals('nguyenvana@example.com', $user->email);
        $this->assertEquals('0123456789', $user->phone);
        $this->assertTrue($user->is_customer);

        // Kiểm tra trong database
        $this->assertDatabaseHas('users', [
            'first_name' => 'Nguyễn',
            'last_name' => 'Văn A',
            'email' => 'nguyenvana@example.com',
            'phone' => '0123456789',
        ]);
    }

    public function test_it_can_get_user_by_id()
    {
        // Tạo người dùng
        $user = User::factory()->create([
            'first_name' => 'Trần',
            'last_name' => 'Thị B',
        ]);

        // Lấy thông tin người dùng
        $foundUser = $this->userService->getUserById($user->id);

        // Kiểm tra thông tin người dùng
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('Trần', $foundUser->first_name);
        $this->assertEquals('Thị B', $foundUser->last_name);
    }

    public function test_it_throws_exception_when_user_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->userService->getUserById('non-existent-id');
    }

    public function test_it_can_update_user()
    {
        // Tạo người dùng
        $user = User::factory()->create([
            'first_name' => 'Lê',
            'last_name' => 'Văn C',
            'email' => 'levanc@example.com',
        ]);

        // Tạo UserDTO với thông tin cập nhật
        $userDTO = new UserDTO(
            first_name: 'Lê',
            last_name: 'Văn C (Đã cập nhật)',
            email: 'levanc_updated@example.com',
            phone: null,
            password: null,
            is_customer: null
        );

        // Cập nhật người dùng
        $updatedUser = $this->userService->updateUser($user->id, $userDTO);

        // Kiểm tra thông tin người dùng
        $this->assertEquals($user->id, $updatedUser->id);
        $this->assertEquals('Lê', $updatedUser->first_name);
        $this->assertEquals('Văn C (Đã cập nhật)', $updatedUser->last_name);
        $this->assertEquals('levanc_updated@example.com', $updatedUser->email);

        // Kiểm tra trong database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_name' => 'Văn C (Đã cập nhật)',
            'email' => 'levanc_updated@example.com',
        ]);
    }

    public function test_it_can_delete_user()
    {
        // Tạo người dùng
        $user = User::factory()->create();
        $userId = $user->id;

        // Xóa người dùng
        $this->userService->deleteUser($user->id);

        // Kiểm tra rằng user đã bị soft delete
        $this->assertSoftDeleted('users', [
            'id' => $userId,
        ]);
    }

    public function test_it_throws_exception_when_deleting_non_existent_user()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->userService->deleteUser('non-existent-id');
    }
}
