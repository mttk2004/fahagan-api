<?php

namespace Tests\Feature\Api\V1\Genre;

use App\Enums\ResponseMessage;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một user và gán các quyền
        $this->adminUser = User::factory()->create([
          'is_customer' => false,
        ]);
        $this->adminUser->givePermissionTo([
          'view_genres',
          'create_genres',
          'edit_genres',
          'delete_genres',
          'restore_genres',
        ]);
    }

    public function test_it_can_get_list_of_genres()
    {
        // Tạo 3 thể loại
        Genre::factory(3)->create();

        // Gọi API danh sách thể loại
        $response = $this->actingAs($this->adminUser)
          ->getJson('/api/v1/genres');

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'data',
            'links',
          ]);
    }

    public function test_it_can_get_genre_details()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Gọi API xem chi tiết thể loại
        $response = $this->actingAs($this->adminUser)
          ->getJson("/api/v1/genres/{$genre->id}");

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'data' => [
              'genre' => [
                'id',
                'type',
                'attributes' => [
                  'name',
                  'slug',
                  'books_count',
                  'description',
                ],
                'relationships',
              ],
            ],
          ]);
    }

    public function test_it_can_get_genre_by_slug()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Gọi API xem chi tiết thể loại theo slug
        $response = $this->actingAs($this->adminUser)
          ->getJson("/api/v1/genres/slug/{$genre->slug}");

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'data' => [
              'genre' => [
                'id',
                'type',
                'attributes',
              ],
            ],
          ]);
    }

    public function test_it_returns_404_when_genre_not_found()
    {
        // Gọi API với ID không tồn tại
        $response = $this->actingAs($this->adminUser)
          ->getJson('/api/v1/genres/999999');

        // Kiểm tra response
        $response->assertStatus(404)
          ->assertJson([
            'status' => 404,
            'message' => ResponseMessage::NOT_FOUND_GENRE->value,
          ]);
    }

    public function test_it_can_create_genre()
    {
        // Tạo dữ liệu để tạo thể loại mới với định dạng trực tiếp
        $genreData = [
          'name' => 'Tiểu thuyết lịch sử',
          'description' => 'Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.',
        ];

        // Gọi API tạo thể loại
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/genres', $genreData);

        // Kiểm tra response
        $response->assertStatus(201)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'genre',
            ],
          ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('genres', [
          'name' => 'Tiểu thuyết lịch sử',
          'slug' => 'tieu-thuyet-lich-su',
          'description' => 'Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.',
        ]);
    }

    public function test_it_creates_slug_automatically_if_not_provided()
    {
        // Tạo dữ liệu không có slug với định dạng trực tiếp
        $genreData = [
          'name' => 'Tiểu thuyết lịch sử',
          'description' => 'Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.',
        ];

        // Gọi API tạo thể loại
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/genres', $genreData);

        // Kiểm tra response
        $response->assertStatus(201);

        // Lấy ID của thể loại vừa tạo
        $genreId = $response->json('data.genre.id');

        // Kiểm tra ID có giá trị
        $this->assertNotNull($genreId);

        // Kiểm tra slug đã được tạo tự động
        $genre = Genre::find($genreId);
        $this->assertNotNull($genre);
        $this->assertEquals('Tiểu thuyết lịch sử', $genre->name);
        $this->assertEquals('tieu-thuyet-lich-su', $genre->slug);

        $this->assertDatabaseHas('genres', [
          'id' => $genreId,
          'name' => 'Tiểu thuyết lịch sử',
          'slug' => 'tieu-thuyet-lich-su',
        ]);
    }

    public function test_it_can_update_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Dữ liệu cập nhật với định dạng trực tiếp
        $updateData = [
          'name' => 'Tên Mới',
          'description' => 'Mô tả mới',
        ];

        // Gọi API cập nhật thể loại
        $response = $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/genres/{$genre->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'genre',
            ],
          ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('genres', [
          'id' => $genre->id,
          'name' => 'Tên Mới',
          'description' => 'Mô tả mới',
        ]);
    }

    public function test_it_updates_slug_when_name_changed_without_new_slug()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create([
          'name' => 'Thể loại cũ',
          'slug' => 'the-loai-cu',
        ]);

        // Dữ liệu cập nhật chỉ có name với định dạng trực tiếp
        $updateData = [
          'name' => 'Thể loại mới',
        ];

        // Gọi API cập nhật thể loại
        $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/genres/{$genre->id}", $updateData);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('genres', [
          'id' => $genre->id,
          'name' => 'Thể loại mới',
          'slug' => 'the-loai-moi',
        ]);
    }

    public function test_it_can_delete_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Gọi API xóa thể loại
        $response = $this->actingAs($this->adminUser)
          ->deleteJson("/api/v1/genres/{$genre->id}");

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa mềm
        $this->assertSoftDeleted('genres', [
          'id' => $genre->id,
        ]);
    }

    public function test_it_can_restore_genre()
    {
        // Tạo và xóa một thể loại
        $genre = Genre::factory()->create();
        $genre->delete();

        // Gọi API khôi phục thể loại
        $response = $this->actingAs($this->adminUser)
          ->postJson("/api/v1/genres/restore/{$genre->id}");

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'genre',
            ],
          ]);

        // Kiểm tra thể loại đã được khôi phục
        $this->assertDatabaseHas('genres', [
          'id' => $genre->id,
          'deleted_at' => null,
        ]);
    }

    public function test_it_requires_authentication_to_list_genres()
    {
        // Gọi API danh sách thể loại không có xác thực
        $response = $this->getJson('/api/v1/genres');

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_requires_authentication_to_create_genre()
    {
        // Tạo dữ liệu để tạo thể loại mới với định dạng trực tiếp
        $genreData = [
          'name' => 'Tiểu thuyết lịch sử',
          'description' => 'Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.',
        ];

        // Gọi API tạo thể loại không có xác thực
        $response = $this->postJson('/api/v1/genres', $genreData);

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_update_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Tạo một user bình thường không có quyền cập nhật thể loại
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Dữ liệu cập nhật với định dạng trực tiếp
        $updateData = [
          'name' => 'Tên Mới',
          'description' => 'Mô tả mới',
        ];

        // Gọi API cập nhật thể loại với user không có quyền
        $response = $this->actingAs($regularUser)
          ->patchJson("/api/v1/genres/{$genre->id}", $updateData);

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_delete_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Tạo một user bình thường không có quyền xóa thể loại
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Gọi API xóa thể loại với user không có quyền
        $response = $this->actingAs($regularUser)
          ->deleteJson("/api/v1/genres/{$genre->id}");

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_validates_input_when_creating_genre()
    {
        // Tạo dữ liệu không hợp lệ (thiếu các trường bắt buộc) với định dạng trực tiếp
        $invalidData = [];

        // Gọi API tạo thể loại với dữ liệu không hợp lệ
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/genres', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
          ->assertJsonValidationErrors([
            'name',
            'description',
          ]);
    }
}
