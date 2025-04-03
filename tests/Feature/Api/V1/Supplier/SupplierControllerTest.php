<?php

namespace Tests\Feature\Api\V1\Supplier;

use App\Models\Book;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một người dùng admin và gán quyền quản lý nhà cung cấp
        $this->adminUser = User::factory()->create([
            'is_customer' => false,
        ]);
        $this->adminUser->givePermissionTo([
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            'restore_suppliers'
        ]);
    }

    public function test_it_can_get_list_of_suppliers()
    {
        // Tạo 3 nhà cung cấp
        Supplier::factory(3)->create();

        // Gọi API danh sách nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/suppliers');

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
            ]);
    }

    public function test_it_can_get_supplier_details()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Gọi API xem chi tiết nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/suppliers/{$supplier->id}");

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'supplier' => [
                        'id',
                        'type',
                        'attributes' => [
                            'name',
                            'phone',
                            'email',
                            'city',
                            'district',
                            'ward',
                            'address_line'
                        ]
                    ]
                ]
            ]);
    }

    public function test_it_returns_404_when_supplier_not_found()
    {
        // Gọi API với ID không tồn tại
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/suppliers/999999');

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Không tìm thấy nhà cung cấp.',
            ]);
    }

    public function test_it_can_create_supplier()
    {
        // Tạo sách để liên kết với nhà cung cấp
        $books = Book::factory(2)->create();

        // Tạo dữ liệu để tạo nhà cung cấp mới
        $supplierData = [
            'name' => 'Nhà Sách Test',
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
            'data' => [
                'relationships' => [
                    'books' => [
                        'data' => $books->map(function ($book) {
                            return ['id' => $book->id];
                        })->toArray()
                    ]
                ]
            ]
        ];

        // Gọi API tạo nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/suppliers', $supplierData);

        // Kiểm tra response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'supplier'
                ]
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Nhà Sách Test',
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ]);

        // Lấy ID của nhà cung cấp vừa tạo
        $supplierId = $response->json('data.supplier.id');

        // Kiểm tra mối quan hệ với sách
        foreach ($books as $book) {
            $this->assertDatabaseHas('book_supplier', [
                'book_id' => $book->id,
                'supplier_id' => $supplierId
            ]);
        }
    }

    public function test_it_can_restore_soft_deleted_supplier()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Xóa mềm nhà cung cấp
        $supplier->delete();

        // Kiểm tra đã bị xóa mềm
        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id
        ]);

        // Gọi API khôi phục nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/suppliers/restore/{$supplier->id}");

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'supplier'
                ]
            ]);

        // Kiểm tra dữ liệu trong database (đã được khôi phục)
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'deleted_at' => null
        ]);
    }

    public function test_it_can_update_supplier()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Tạo sách mới để liên kết
        $books = Book::factory(3)->create();

        // Dữ liệu cập nhật
        $updateData = [
            'name' => 'Tên Mới',
            'phone' => '9876543210',
            'email' => 'updated@example.com',
            'books' => $books->pluck('id')->toArray()
        ];

        // Gọi API cập nhật nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/suppliers/{$supplier->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'supplier'
                ]
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Tên Mới',
            'phone' => '9876543210',
            'email' => 'updated@example.com',
        ]);

        // Kiểm tra mối quan hệ với sách
        foreach ($books as $book) {
            $this->assertDatabaseHas('book_supplier', [
                'book_id' => $book->id,
                'supplier_id' => $supplier->id
            ]);
        }
    }

    public function test_it_can_delete_supplier()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Liên kết với sách
        $books = Book::factory(2)->create();
        $supplier->suppliedBooks()->attach($books->pluck('id')->toArray());

        // Gọi API xóa nhà cung cấp
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}");

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa mềm
        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);

        // Kiểm tra mối quan hệ với sách đã bị xóa
        foreach ($books as $book) {
            $this->assertDatabaseMissing('book_supplier', [
                'book_id' => $book->id,
                'supplier_id' => $supplier->id
            ]);
        }
    }

    public function test_it_requires_authentication_to_create_supplier()
    {
        // Tạo dữ liệu để tạo nhà cung cấp mới
        $supplierData = [
            'name' => 'Nhà Sách Test',
            'phone' => '0123456789',
            'email' => 'test@example.com',
            'city' => 'Hà Nội',
            'district' => 'Cầu Giấy',
            'ward' => 'Dịch Vọng',
            'address_line' => 'Số 1 Đường ABC',
        ];

        // Gọi API tạo nhà cung cấp không có xác thực
        $response = $this->postJson('/api/v1/suppliers', $supplierData);

        // Kiểm tra response phải trả về lỗi 403 vì cố gắng truy cập mà không có quyền
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_update_supplier()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Tạo một user bình thường không có quyền cập nhật nhà cung cấp
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Dữ liệu cập nhật
        $updateData = [
            'name' => 'Tên Mới',
            'phone' => '9876543210',
            'email' => 'updated@example.com',
        ];

        // Gọi API cập nhật nhà cung cấp với tài khoản không có quyền
        $response = $this->actingAs($regularUser)
            ->patchJson("/api/v1/suppliers/{$supplier->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_delete_supplier()
    {
        // Tạo một nhà cung cấp
        $supplier = Supplier::factory()->create();

        // Tạo một user bình thường không có quyền xóa nhà cung cấp
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Gọi API xóa nhà cung cấp với tài khoản không có quyền
        $response = $this->actingAs($regularUser)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}");

        // Kiểm tra response
        $response->assertStatus(403);
    }

    public function test_it_validates_input_when_creating_supplier()
    {
        // Tạo dữ liệu không hợp lệ (thiếu name là trường bắt buộc)
        $invalidData = [
            'phone' => '0123456789',
            'email' => 'invalid-email', // Email không đúng định dạng
        ];

        // Gọi API tạo nhà cung cấp với dữ liệu không hợp lệ
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/suppliers', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }
}
