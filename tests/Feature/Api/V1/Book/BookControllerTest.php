<?php

namespace Tests\Feature\Api\V1;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User
     */
    private $user;

    private Publisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một người dùng và gán quyền quản lý sách
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'create_books',
            'edit_books',
            'delete_books',
        ]);

        // Tạo một Publisher để sử dụng trong các test
        $this->publisher = Publisher::factory()->create();
    }

    public function test_it_can_get_list_of_books()
    {
        // Tạo một số sách
        Book::factory()->count(5)->create([
            'publisher_id' => $this->publisher->id,
        ]);

        // Gọi API và kiểm tra response
        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
    }

    public function test_it_can_get_a_book_by_id()
    {
        // Tạo một book mới
        $book = Book::factory()->create([
            'publisher_id' => $this->publisher->id,
        ]);

        // Tạo một số tác giả và thể loại và liên kết với sách
        $authors = Author::factory()->count(2)->create();
        $genres = Genre::factory()->count(2)->create();

        $book->authors()->attach($authors->pluck('id')->toArray());
        $book->genres()->attach($genres->pluck('id')->toArray());

        // Gọi API với ID của book và kiểm tra response
        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_it_returns_404_when_book_not_found()
    {
        // Gọi API với một ID không tồn tại
        $invalidId = 'non-existent-id';
        $response = $this->getJson("/api/v1/books/{$invalidId}");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 404,
                'message' => 'Không tìm thấy sách.',
            ]);
    }

    public function test_it_can_create_a_new_book()
    {
        // Tạo một số tác giả và thể loại để liên kết với sách
        $authors = Author::factory()->count(2)->create();
        $genres = Genre::factory()->count(2)->create();

        // Dữ liệu cần thiết để tạo sách
        $bookData = [
            'data' => [
                'attributes' => [
                    'title' => 'Sách Test',
                    'description' => 'Mô tả sách test',
                    'price' => 250000,
                    'edition' => 1,
                    'pages' => 200,
                    'publication_date' => '2023-01-01',
                    'image_url' => 'https://example.com/book.jpg',
                    'sold_count' => 0,
                ],
                'relationships' => [
                    'publisher' => [
                        'id' => $this->publisher->id,
                    ],
                    'authors' => [
                        'data' => $authors->map(function ($author) {
                            return ['id' => $author->id];
                        })->toArray(),
                    ],
                    'genres' => [
                        'data' => $genres->map(function ($genre) {
                            return ['id' => $genre->id];
                        })->toArray(),
                    ],
                ],
            ],
        ];

        // Gọi API với dữ liệu và sanction token
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/books', $bookData);

        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $json) {
                $json->has('status')
                    ->where('status', 201)
                    ->has('message')
                    ->has('data.book')
                    ->has('data.book.id')
                    ->where('data.book.attributes.title', 'Sách Test')
                    ->where('data.book.attributes.description', 'Mô tả sách test')
                    ->has('data.book.relationships.authors.data')
                    ->has('data.book.relationships.genres.data');
            });

        // Kiểm tra trong database
        $this->assertDatabaseHas('books', [
            'title' => 'Sách Test',
            'description' => 'Mô tả sách test',
            'price' => 250000,
            'edition' => 1,
        ]);
    }

    public function test_it_validates_required_fields_when_creating_a_book()
    {
        // Dữ liệu thiếu thông tin bắt buộc
        $bookData = [
            'data' => [
                'attributes' => [
                    // missing title
                    'description' => 'Mô tả sách test',
                    // missing price
                    // missing edition
                ],
            ],
        ];

        // Gọi API với dữ liệu thiếu
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.title',
                'data.attributes.price',
                'data.attributes.edition',
            ]);
    }

    public function test_it_can_update_a_book()
    {
        // Tạo một book mới
        $book = Book::factory()->create([
            'publisher_id' => $this->publisher->id,
            'description' => 'Mô tả ban đầu',
        ]);

        // Dữ liệu cập nhật
        $updateData = [
            'data' => [
                'attributes' => [
                    'title' => 'Tiêu đề đã cập nhật',
                    'description' => 'Mô tả đã cập nhật',
                ],
            ],
        ];

        // Gọi API với dữ liệu cập nhật
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('status')
                    ->where('status', 200)
                    ->has('message')
                    ->has('data.book');
            });

        // Kiểm tra trong database
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Tiêu đề đã cập nhật',
            'description' => 'Mô tả đã cập nhật',
        ]);
    }

    public function test_it_prevents_updating_to_existing_title_and_edition_combination()
    {
        // Tạo hai books với title và edition khác nhau
        $book1 = Book::factory()->create([
            'title' => 'Sách thứ nhất',
            'edition' => 1,
            'publisher_id' => $this->publisher->id,
        ]);

        $book2 = Book::factory()->create([
            'title' => 'Sách thứ hai',
            'edition' => 2,
            'publisher_id' => $this->publisher->id,
        ]);

        // Dữ liệu cập nhật book2 thành title và edition giống book1
        $updateData = [
            'data' => [
                'attributes' => [
                    'title' => 'Sách thứ nhất',
                    'edition' => 1,
                ],
            ],
        ];

        // Gọi API với dữ liệu cập nhật
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/books/{$book2->id}", $updateData);

        $response->assertStatus(422);
    }

    public function test_it_can_delete_a_book()
    {
        // Tạo một book mới
        $book = Book::factory()->create([
            'publisher_id' => $this->publisher->id,
        ]);

        // Gọi API để xóa book
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Sách đã được xóa thành công.',
            ]);

        // Kiểm tra rằng book đã bị soft delete
        $this->assertSoftDeleted('books', [
            'id' => $book->id,
        ]);
    }

    public function test_it_confirms_authentication_requirements_for_api()
    {
        // Đảm bảo người dùng chưa đăng nhập không thể truy cập các API yêu cầu xác thực
        $book = Book::factory()->create([
            'publisher_id' => $this->publisher->id,
        ]);

        // Dữ liệu sách mới
        $newBookData = [
            'data' => [
                'attributes' => [
                    'title' => 'Sách Test Auth',
                    'description' => 'Mô tả sách test',
                    'price' => 250000,
                    'edition' => 1,
                    'pages' => 200,
                    'publication_date' => '2023-01-01',
                    'image_url' => 'https://example.com/book.jpg',
                ],
                'relationships' => [
                    'publisher' => [
                        'id' => $this->publisher->id,
                    ],
                ],
            ],
        ];

        // Truy cập API lấy danh sách sách (không yêu cầu xác thực)
        $listResponse = $this->getJson('/api/v1/books');
        $listResponse->assertStatus(200);

        // Truy cập API lấy chi tiết sách (không yêu cầu xác thực)
        $showResponse = $this->getJson("/api/v1/books/{$book->id}");
        $showResponse->assertStatus(200);

        // Kiểm tra rằng những API được ủy quyền (không phải là API công khai) có thể truy cập bởi người dùng có quyền
        $authorizedCreateResponse = $this->actingAs($this->user)
            ->postJson('/api/v1/books', $newBookData);
        $authorizedCreateResponse->assertStatus(201);

        // Kiểm tra rằng dữ liệu đã được lưu thành công
        $this->assertDatabaseHas('books', [
            'title' => 'Sách Test Auth',
        ]);
    }
}
