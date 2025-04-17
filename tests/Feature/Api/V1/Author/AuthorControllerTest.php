<?php

namespace Tests\Feature\Api\V1\Author;

use App\Enums\ResponseMessage;
use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Database\Seeders\PublisherSeeder;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một người dùng và gán các quyền
        $this->adminUser = User::factory()->create([
          'is_customer' => false,
        ]);
        $this->adminUser->givePermissionTo([
          'create_authors',
          'edit_authors',
          'delete_authors',
        ]);
    }

    public function test_it_can_get_list_of_authors()
    {
        // Tạo 3 tác giả
        Author::factory(3)->create();

        // Gọi API danh sách tác giả
        $response = $this->getJson('/api/v1/authors');

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'data',
            'links',
          ]);
    }

    public function test_it_can_get_author_details()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Gọi API xem chi tiết tác giả
        $response = $this->getJson("/api/v1/authors/{$author->id}");

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'data' => [
              'author' => [
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

    public function test_it_returns_404_when_author_not_found()
    {
        // Gọi API với ID không tồn tại
        $response = $this->getJson('/api/v1/authors/999999');

        // Kiểm tra response
        $response->assertStatus(404)
          ->assertJson([
            'status' => 404,
            'message' => ResponseMessage::NOT_FOUND_AUTHOR->value,
          ]);
    }

    public function test_it_can_create_author_with_json_api_format()
    {
        // Tạo dữ liệu để tạo tác giả mới
        $authorData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
              'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
            ],
          ],
        ];

        // Gọi API tạo tác giả
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/authors', $authorData);

        // Kiểm tra response
        $response->assertStatus(201)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'author',
            ],
          ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('authors', [
          'name' => 'Nguyễn Nhật Ánh',
          'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
          'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
        ]);
    }

    public function test_it_rejects_direct_format_for_creating_author()
    {
        // Tạo dữ liệu theo direct format (không còn được hỗ trợ)
        $authorData = [
          'name' => 'Nguyễn Nhật Ánh',
          'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
          'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
        ];

        // Gọi API tạo tác giả
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/authors', $authorData);

        // Kiểm tra response - phải trả về lỗi validation vì thiếu cấu trúc JSON:API
        $response->assertStatus(422);
    }

    public function test_it_can_create_author_with_relationships()
    {
        // Đảm bảo có nhà xuất bản trong database để tạo sách
        $this->seed(PublisherSeeder::class);

        // Tạo 2 sách
        $books = Book::factory()->count(2)->create();

        // Tạo dữ liệu để tạo tác giả mới với relationships
        $authorData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
              'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
            ],
            'relationships' => [
              'books' => [
                'data' => [
                  ['id' => $books[0]->id, 'type' => 'books'],
                  ['id' => $books[1]->id, 'type' => 'books'],
                ],
              ],
            ],
          ],
        ];

        // Gọi API tạo tác giả
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/authors', $authorData);

        // Kiểm tra response
        $response->assertStatus(201);

        // Lấy ID của tác giả mới tạo từ response
        $authorId = $response->json('data.author.id');

        // Kiểm tra relationships trong database
        foreach ($books as $book) {
            $this->assertDatabaseHas('author_book', [
              'author_id' => $authorId,
              'book_id' => $book->id,
            ]);
        }
    }

    public function test_it_can_update_author()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Dữ liệu cập nhật
        $updateData = [
          'data' => [
            'attributes' => [
              'name' => 'Tên Mới',
              'biography' => 'Tiểu sử mới',
            ],
          ],
        ];

        // Gọi API cập nhật tác giả
        $response = $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/authors/{$author->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'status',
            'message',
            'data' => [
              'author',
            ],
          ]);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('authors', [
          'id' => $author->id,
          'name' => 'Tên Mới',
          'biography' => 'Tiểu sử mới',
        ]);
    }

    public function test_it_rejects_direct_format_for_updating_author()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Dữ liệu cập nhật theo direct format (không còn được hỗ trợ)
        $updateData = [
          'name' => 'Tên Mới',
          'biography' => 'Tiểu sử mới',
          'image_url' => 'https://example.com/newimage.jpg',
        ];

        // Gọi API cập nhật tác giả
        $response = $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/authors/{$author->id}", $updateData);

        // Kiểm tra response - API trả về 400 Bad Request vì kiểm tra isEmptyUpdateData trước validation
        $response->assertStatus(400)
          ->assertJson([
            'status' => 400,
            'message' => 'Không có dữ liệu nào để cập nhật.',
          ]);
    }

    public function test_it_can_update_author_relationships()
    {
        // Đảm bảo có nhà xuất bản trong database để tạo sách
        $this->seed(PublisherSeeder::class);

        // Tạo một tác giả và gắn với 2 sách
        $author = Author::factory()->create();
        $oldBooks = Book::factory()->count(2)->create();
        $author->writtenBooks()->attach($oldBooks->pluck('id'));

        // Tạo 2 sách mới
        $newBooks = Book::factory()->count(2)->create();

        // Dữ liệu cập nhật với relationships mới
        $updateData = [
          'data' => [
            'attributes' => [
              'name' => 'Tên Mới',
            ],
            'relationships' => [
              'books' => [
                'data' => [
                  ['id' => $newBooks[0]->id, 'type' => 'books'],
                  ['id' => $newBooks[1]->id, 'type' => 'books'],
                ],
              ],
            ],
          ],
        ];

        // Gọi API cập nhật tác giả
        $response = $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/authors/{$author->id}", $updateData);

        // Kiểm tra response
        $response->assertStatus(200);

        // Kiểm tra sách cũ không còn liên kết
        foreach ($oldBooks as $book) {
            $this->assertDatabaseMissing('author_book', [
              'author_id' => $author->id,
              'book_id' => $book->id,
            ]);
        }

        // Kiểm tra sách mới đã được liên kết
        foreach ($newBooks as $book) {
            $this->assertDatabaseHas('author_book', [
              'author_id' => $author->id,
              'book_id' => $book->id,
            ]);
        }
    }

    public function test_it_can_delete_author()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Gọi API xóa tác giả
        $response = $this->actingAs($this->adminUser)
          ->deleteJson("/api/v1/authors/{$author->id}");

        // Kiểm tra response
        $response->assertStatus(204);

        // Kiểm tra dữ liệu đã bị xóa mềm
        $this->assertSoftDeleted('authors', [
          'id' => $author->id,
        ]);
    }

    public function test_it_requires_authentication_to_create_author()
    {
        // Tạo dữ liệu để tạo tác giả mới
        $authorData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
              'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
            ],
          ],
        ];

        // Gọi API tạo tác giả không có xác thực
        $response = $this->postJson('/api/v1/authors', $authorData);

        // Kiểm tra response phải trả về lỗi 403 vì cố gắng truy cập mà không có quyền
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_update_author()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Tạo một user bình thường không có quyền cập nhật tác giả
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Dữ liệu cập nhật
        $updateData = [
          'data' => [
            'attributes' => [
              'name' => 'Tên Mới',
              'biography' => 'Tiểu sử mới',
            ],
          ],
        ];

        // Gọi API cập nhật tác giả với user không có quyền
        $response = $this->actingAs($regularUser)
          ->patchJson("/api/v1/authors/{$author->id}", $updateData);

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_requires_authorization_to_delete_author()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Tạo một user bình thường không có quyền xóa tác giả
        $regularUser = User::factory()->create(['is_customer' => true]);

        // Gọi API xóa tác giả với user không có quyền
        $response = $this->actingAs($regularUser)
          ->deleteJson("/api/v1/authors/{$author->id}");

        // Kiểm tra response phải trả về lỗi 403
        $response->assertStatus(403);
    }

    public function test_it_validates_input_when_creating_author()
    {
        // Tạo dữ liệu thiếu trường bắt buộc
        $invalidData = [
          'data' => [
            'attributes' => [
              // Thiếu trường 'name' bắt buộc
              'biography' => 'Tiểu sử...',
            ],
          ],
        ];

        // Gọi API tạo tác giả
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/authors', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422)
          ->assertJsonValidationErrors(['data.attributes.name']);
    }

    public function test_it_validates_relationships_format()
    {
        // Tạo dữ liệu với relationships không hợp lệ
        $invalidData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tiểu sử...',
              'image_url' => 'https://example.com/nguyen-nhat-anh.jpg',
            ],
            'relationships' => [
              'books' => [
                'data' => [
                  ['id' => 1], // Thiếu trường 'type'
                ],
              ],
            ],
          ],
        ];

        // Gọi API tạo tác giả
        $response = $this->actingAs($this->adminUser)
          ->postJson('/api/v1/authors', $invalidData);

        // Kiểm tra response
        $response->assertStatus(422);
    }

    public function test_it_handles_pagination_when_listing_authors()
    {
        // Tạo 15 tác giả
        Author::factory()->count(15)->create();

        // Gọi API danh sách tác giả với tham số phân trang
        $response = $this->getJson('/api/v1/authors?page=2&per_page=5');

        // Kiểm tra response
        $response->assertStatus(200)
          ->assertJsonStructure([
            'data',
            'links',
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

        // Kiểm tra thông tin phân trang
        $response->assertJsonPath('meta.current_page', 2)
          ->assertJsonPath('meta.total', 15);
    }

    public function test_it_returns_error_when_no_data_to_update()
    {
        // Tạo một tác giả
        $author = Author::factory()->create();

        // Dữ liệu cập nhật rỗng
        $emptyUpdateData = [
          'data' => [
            'attributes' => [],
          ],
        ];

        // Gọi API cập nhật tác giả
        $response = $this->actingAs($this->adminUser)
          ->patchJson("/api/v1/authors/{$author->id}", $emptyUpdateData);

        // Kiểm tra response
        $response->assertStatus(400)
          ->assertJson([
            'status' => 400,
            'message' => 'Không có dữ liệu nào để cập nhật.',
          ]);
    }
}
