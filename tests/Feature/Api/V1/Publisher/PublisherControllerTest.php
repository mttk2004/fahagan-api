<?php

namespace Tests\Feature\Api\V1\Publisher;

use App\Models\Publisher;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublisherControllerTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một người dùng admin và gán quyền quản lý publisher
        $this->adminUser = User::factory()->create([
            'is_customer' => false,
        ]);
        $this->adminUser->givePermissionTo([
            'create_publishers',
            'edit_publishers',
            'delete_publishers',
        ]);
    }

    public function test_it_can_get_list_of_publishers()
    {
        // Tạo 3 nhà xuất bản
        Publisher::factory(3)->create();

        // Gọi API danh sách nhà xuất bản
        $response = $this->getJson('/api/v1/publishers');

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
            ]);
    }

    public function test_it_can_get_publisher_details()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create();

        // Gọi API xem chi tiết nhà xuất bản
        $response = $this->getJson("/api/v1/publishers/{$publisher->id}");

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'publisher' => [
                        'id',
                        'type',
                        'attributes' => [
                            'name',
                            'biography',
                        ],
                        'relationships',
                    ],
                ],
            ]);
    }

    public function test_it_returns_404_when_publisher_not_found()
    {
        // Gọi API với ID không tồn tại
        $response = $this->getJson('/api/v1/publishers/999999');

        // Kiểm tra response
        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Không tìm thấy nhà xuất bản.',
            ]);
    }

    public function test_it_can_create_publisher()
    {
        // Tạo dữ liệu để tạo nhà xuất bản mới
        $publisherData = [
            'data' => [
                'attributes' => [
                    'name' => 'NXB Văn Học',
                    'biography' => 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.',
                ],
            ],
        ];

        // Gọi API tạo nhà xuất bản
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/publishers', $publisherData);

        // Kiểm tra response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'publisher',
                ],
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('publishers', [
            'name' => 'NXB Văn Học',
            'biography' => 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.',
        ]);
    }

    public function test_it_can_restore_soft_deleted_publisher()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create([
            'name' => 'NXB Khoa Học và Kỹ Thuật',
            'biography' => 'Nhà xuất bản chuyên về sách khoa học - kỹ thuật.',
        ]);

        // Xóa mềm nhà xuất bản
        $publisher->delete();

        // Tạo dữ liệu để tạo lại nhà xuất bản đã bị xóa mềm
        $publisherData = [
            'data' => [
                'attributes' => [
                    'name' => 'NXB Khoa Học và Kỹ Thuật',
                    'biography' => 'Nhà xuất bản chuyên về sách khoa học - kỹ thuật và công nghệ.',
                ],
            ],
        ];

        // Gọi API tạo nhà xuất bản
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/publishers', $publisherData);

        // Kiểm tra response
        $response->assertStatus(201);

        // Kiểm tra dữ liệu trong database (đã được khôi phục và cập nhật)
        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'NXB Khoa Học và Kỹ Thuật',
            'biography' => 'Nhà xuất bản chuyên về sách khoa học - kỹ thuật và công nghệ.',
        ]);

        // Đảm bảo bản ghi không còn trong danh sách đã xóa mềm
        $this->assertDatabaseMissing('publishers', [
            'id' => $publisher->id,
            'deleted_at' => $publisher->deleted_at,
        ]);
    }

    public function test_it_can_update_publisher()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create();

        // Dữ liệu cập nhật
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Tên Mới',
                    'biography' => 'Giới thiệu mới',
                ],
            ],
        ];

        // Gọi API cập nhật nhà xuất bản
        $response = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/publishers/{$publisher->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'publisher',
                ],
            ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'Tên Mới',
            'biography' => 'Giới thiệu mới',
        ]);
    }

    public function test_it_can_delete_publisher()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create();

        // Gọi API xóa nhà xuất bản
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/publishers/{$publisher->id}");

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa mềm
        $this->assertSoftDeleted('publishers', [
            'id' => $publisher->id,
        ]);
    }

    public function test_it_requires_authentication_to_create_publisher()
    {
        // Tạo dữ liệu để tạo nhà xuất bản mới
        $publisherData = [
            'data' => [
                'attributes' => [
                    'name' => 'NXB Văn Học',
                    'biography' => 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.',
                ],
            ],
        ];

        // Gọi API tạo nhà xuất bản không có xác thực
        $response = $this->postJson('/api/v1/publishers', $publisherData);

        // Kiểm tra response phải trả về lỗi 403 vì cố gắng truy cập mà không có quyền
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_update_publisher()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create();

        // Tạo một user bình thường không có quyền cập nhật nhà xuất bản
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Dữ liệu cập nhật
        $updateData = [
            'data' => [
                'attributes' => [
                    'name' => 'Tên Mới',
                    'biography' => 'Giới thiệu mới',
                ],
            ],
        ];

        // Gọi API cập nhật nhà xuất bản với user không có quyền
        $response = $this->actingAs($regularUser)
            ->patchJson("/api/v1/publishers/{$publisher->id}", $updateData);

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_delete_publisher()
    {
        // Tạo một nhà xuất bản
        $publisher = Publisher::factory()->create();

        // Tạo một user bình thường không có quyền xóa nhà xuất bản
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Gọi API xóa nhà xuất bản với user không có quyền
        $response = $this->actingAs($regularUser)
            ->deleteJson("/api/v1/publishers/{$publisher->id}");

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_validates_input_when_creating_publisher()
    {
        // Tạo dữ liệu thiếu trường bắt buộc
        $invalidData = [
            'data' => [
                'attributes' => [
                    // Thiếu trường 'name' bắt buộc
                    'biography' => 'Giới thiệu...',
                ],
            ],
        ];

        // Gọi API tạo nhà xuất bản
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/publishers', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.name']);
    }
}
