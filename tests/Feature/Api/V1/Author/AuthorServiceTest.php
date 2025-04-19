<?php

namespace Tests\Feature\Api\V1\Author;

use App\DTOs\Author\AuthorDTO;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthorServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthorService $authorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorService = new AuthorService();
    }

    public function test_it_can_get_all_authors()
    {
        // Tạo 5 tác giả
        Author::factory()->count(5)->create();

        // Tạo request rỗng
        $request = new Request();

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request);

        // Kiểm tra số lượng tác giả
        $this->assertEquals(5, $authors->count());
    }

    public function test_it_can_filter_authors_by_name()
    {
        // Tạo 3 tác giả với tên khác nhau
        Author::factory()->create(['name' => 'Nguyễn Nhật Ánh']);
        Author::factory()->create(['name' => 'Tô Hoài']);
        Author::factory()->create(['name' => 'Nam Cao']);

        // Tạo request với filter theo tên
        $request = new Request(['filter' => ['name' => 'Nguyễn']]);

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request);

        // Kiểm tra số lượng tác giả
        $this->assertEquals(1, $authors->count());
        $this->assertEquals('Nguyễn Nhật Ánh', $authors->first()->name);
    }

    public function test_it_can_filter_authors_by_name_partially()
    {
        // Tạo 3 tác giả với tên khác nhau
        Author::factory()->create(['name' => 'Nguyễn Nhật Ánh']);
        Author::factory()->create(['name' => 'Nguyễn Ngọc Tư']);
        Author::factory()->create(['name' => 'Tô Hoài']);

        // Tạo request với filter theo tên
        $request = new Request(['filter' => ['name' => 'Nguyễn']]);

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request);

        // Kiểm tra số lượng tác giả
        $this->assertEquals(2, $authors->count());
        // Kiểm tra tên tác giả có chứa 'Nguyễn'
        foreach ($authors as $author) {
            $this->assertStringContainsString('Nguyễn', $author->name);
        }
    }

    public function test_it_can_sort_authors()
    {
        // Tạo 3 tác giả
        Author::factory()->create(['name' => 'A Author']);
        Author::factory()->create(['name' => 'B Author']);
        Author::factory()->create(['name' => 'C Author']);

        // Tạo request với sort theo name giảm dần
        $request = new Request(['sort' => '-name']);

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request);

        // Kiểm tra thứ tự sắp xếp
        $this->assertEquals('C Author', $authors[0]->name);
        $this->assertEquals('B Author', $authors[1]->name);
        $this->assertEquals('A Author', $authors[2]->name);
    }

    public function test_it_can_sort_authors_by_name_ascending()
    {
        // Tạo 3 tác giả với tên theo thứ tự ABC
        Author::factory()->create(['name' => 'B Author']);
        Author::factory()->create(['name' => 'A Author']);
        Author::factory()->create(['name' => 'C Author']);

        // Tạo request với sort theo name tăng dần
        $request = new Request(['sort' => 'name']);

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request);

        // Kiểm tra thứ tự sắp xếp tăng dần
        $this->assertEquals('A Author', $authors[0]->name);
        $this->assertEquals('B Author', $authors[1]->name);
        $this->assertEquals('C Author', $authors[2]->name);
    }

    public function test_it_can_get_paginated_authors()
    {
        // Tạo 10 tác giả
        Author::factory()->count(10)->create();

        // Tạo request với phân trang
        $request = new Request(['per_page' => '3']);

        // Thiết lập trang hiện tại - đây là điều Laravel thực hiện tự động trong ứng dụng thực tế
        // Chúng ta cần thiết lập thủ công trong test
        app('request')->merge(['page' => '2']);

        // Lấy danh sách tác giả
        $authors = $this->authorService->getAllAuthors($request, 3);

        // Kiểm tra phân trang
        $this->assertEquals(2, $authors->currentPage());
        $this->assertEquals(3, $authors->perPage());
        $this->assertEquals(10, $authors->total());
    }

    public function test_it_can_create_author()
    {
        // Tạo AuthorDTO
        $authorDTO = new AuthorDTO(
            name: 'Nguyễn Nhật Ánh',
            biography: 'Tiểu sử Nguyễn Nhật Ánh',
            image_url: 'https://example.com/authors/nguyen-nhat-anh.jpg',
        );

        // Tạo tác giả mới
        $author = $this->authorService->createAuthor($authorDTO);

        // Kiểm tra thông tin tác giả
        $this->assertEquals('Nguyễn Nhật Ánh', $author->name);
        $this->assertEquals('Tiểu sử Nguyễn Nhật Ánh', $author->biography);
        $this->assertEquals('https://example.com/authors/nguyen-nhat-anh.jpg', $author->image_url);

        // Kiểm tra trong database
        $this->assertDatabaseHas('authors', [
          'name' => 'Nguyễn Nhật Ánh',
          'biography' => 'Tiểu sử Nguyễn Nhật Ánh',
          'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
        ]);
    }

    public function test_it_can_get_author_by_id()
    {
        // Tạo tác giả
        $author = Author::factory()->create([
          'name' => 'Tô Hoài',
          'biography' => 'Tiểu sử Tô Hoài',
        ]);

        // Lấy thông tin tác giả
        $foundAuthor = $this->authorService->getAuthorById($author->id);

        // Kiểm tra thông tin tác giả
        $this->assertEquals($author->id, $foundAuthor->id);
        $this->assertEquals('Tô Hoài', $foundAuthor->name);
        $this->assertEquals('Tiểu sử Tô Hoài', $foundAuthor->biography);
    }

    public function test_it_throws_exception_when_author_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->authorService->getAuthorById(999999);
    }

    public function test_it_can_update_author()
    {
        // Tạo tác giả
        $author = Author::factory()->create([
          'name' => 'Tô Hoài',
          'biography' => 'Tiểu sử Tô Hoài',
        ]);

        // Tạo AuthorDTO với thông tin cập nhật
        $authorDTO = new AuthorDTO(
            name: 'Tô Hoài (Đã cập nhật)',
            biography: 'Tiểu sử Tô Hoài (Đã cập nhật)',
            image_url: null,
        );

        // Cập nhật tác giả
        $updatedAuthor = $this->authorService->updateAuthor($author->id, $authorDTO);

        // Kiểm tra thông tin tác giả
        $this->assertEquals($author->id, $updatedAuthor->id);
        $this->assertEquals('Tô Hoài (Đã cập nhật)', $updatedAuthor->name);
        $this->assertEquals('Tiểu sử Tô Hoài (Đã cập nhật)', $updatedAuthor->biography);

        // Kiểm tra trong database
        $this->assertDatabaseHas('authors', [
          'id' => $author->id,
          'name' => 'Tô Hoài (Đã cập nhật)',
          'biography' => 'Tiểu sử Tô Hoài (Đã cập nhật)',
        ]);
    }

    public function test_it_can_update_author_partially()
    {
        // Tạo tác giả
        $author = Author::factory()->create([
          'name' => 'Original Name',
          'biography' => 'Original Biography',
          'image_url' => 'original-image.jpg',
        ]);

        // Tạo AuthorDTO chỉ cập nhật tên
        $authorDTO = new AuthorDTO(
            name: 'Updated Name',
            biography: null,
            image_url: null,
        );

        // Cập nhật tác giả
        $updatedAuthor = $this->authorService->updateAuthor($author->id, $authorDTO);

        // Kiểm tra thông tin tác giả - chỉ tên được cập nhật
        $this->assertEquals('Updated Name', $updatedAuthor->name);
        $this->assertEquals('Original Biography', $updatedAuthor->biography);
        $this->assertEquals('original-image.jpg', $updatedAuthor->image_url);
    }

    public function test_it_can_update_only_biography()
    {
        // Tạo tác giả
        $author = Author::factory()->create([
          'name' => 'Original Name',
          'biography' => 'Original Biography',
          'image_url' => 'original-image.jpg',
        ]);

        // Tạo AuthorDTO chỉ cập nhật tiểu sử
        $authorDTO = new AuthorDTO(
            name: null,
            biography: 'Updated Biography',
            image_url: null,
        );

        // Cập nhật tác giả
        $updatedAuthor = $this->authorService->updateAuthor($author->id, $authorDTO);

        // Kiểm tra thông tin tác giả - chỉ biography được cập nhật
        $this->assertEquals('Original Name', $updatedAuthor->name);
        $this->assertEquals('Updated Biography', $updatedAuthor->biography);
        $this->assertEquals('original-image.jpg', $updatedAuthor->image_url);
    }

    public function test_it_can_update_only_image_url()
    {
        // Tạo tác giả
        $author = Author::factory()->create([
          'name' => 'Original Name',
          'biography' => 'Original Biography',
          'image_url' => 'original-image.jpg',
        ]);

        // Tạo AuthorDTO chỉ cập nhật ảnh
        $authorDTO = new AuthorDTO(
            name: null,
            biography: null,
            image_url: 'new-image.jpg',
        );

        // Cập nhật tác giả
        $updatedAuthor = $this->authorService->updateAuthor($author->id, $authorDTO);

        // Kiểm tra thông tin tác giả - chỉ image_url được cập nhật
        $this->assertEquals('Original Name', $updatedAuthor->name);
        $this->assertEquals('Original Biography', $updatedAuthor->biography);
        $this->assertEquals('new-image.jpg', $updatedAuthor->image_url);
    }

    public function test_it_can_delete_author()
    {
        // Tạo tác giả
        $author = Author::factory()->create();
        $authorId = $author->id;

        // Xóa tác giả
        $this->authorService->deleteAuthor($author->id);

        // Kiểm tra tác giả đã bị xóa mềm
        $this->assertSoftDeleted('authors', [
          'id' => $authorId,
        ]);
    }

    public function test_it_throws_exception_when_deleting_non_existent_author()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->authorService->deleteAuthor(999999);
    }
}
