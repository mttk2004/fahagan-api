<?php

namespace Tests\Feature\Api\V1\Author;

use App\DTOs\Author\AuthorDTO;
use App\Models\Author;
use App\Models\Book;
use App\Services\AuthorService;
use Database\Seeders\PublisherSeeder;
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

    public function test_it_can_create_author()
    {
        // Tạo AuthorDTO
        $authorDTO = new AuthorDTO(
            name: 'Nguyễn Nhật Ánh',
            biography: 'Tiểu sử Nguyễn Nhật Ánh',
            image_url: 'https://example.com/authors/nguyen-nhat-anh.jpg',
            book_ids: []
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

    public function test_it_can_create_author_with_books()
    {
        // Đảm bảo có nhà xuất bản trong database để tạo sách
        $this->seed(PublisherSeeder::class);

        // Tạo 2 sách
        $books = Book::factory()->count(2)->create();
        $bookIds = $books->pluck('id')->toArray();

        // Tạo AuthorDTO với books
        $authorDTO = new AuthorDTO(
            name: 'Nguyễn Nhật Ánh',
            biography: 'Tiểu sử Nguyễn Nhật Ánh',
            image_url: 'https://example.com/authors/nguyen-nhat-anh.jpg',
            book_ids: $bookIds
        );

        // Tạo tác giả mới
        $author = $this->authorService->createAuthor($authorDTO);

        // Kiểm tra thông tin tác giả
        $this->assertEquals('Nguyễn Nhật Ánh', $author->name);

        // Kiểm tra quan hệ với sách
        $this->assertCount(2, $author->writtenBooks);
        foreach ($bookIds as $bookId) {
            $this->assertDatabaseHas('author_book', [
                'author_id' => $author->id,
                'book_id' => $bookId,
            ]);
        }
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
            book_ids: []
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

    public function test_it_can_update_author_books()
    {
        // Đảm bảo có nhà xuất bản trong database để tạo sách
        $this->seed(PublisherSeeder::class);

        // Tạo tác giả và sách
        $author = Author::factory()->create();
        $oldBooks = Book::factory()->count(2)->create();
        $author->writtenBooks()->attach($oldBooks->pluck('id'));

        // Tạo sách mới
        $newBooks = Book::factory()->count(3)->create();
        $newBookIds = $newBooks->pluck('id')->toArray();

        // Tạo AuthorDTO với thông tin cập nhật
        $authorDTO = new AuthorDTO(
            name: 'Tên mới',
            biography: null,
            image_url: null,
            book_ids: $newBookIds
        );

        // Cập nhật tác giả
        $updatedAuthor = $this->authorService->updateAuthor($author->id, $authorDTO);

        // Kiểm tra thông tin tác giả
        $this->assertEquals($author->id, $updatedAuthor->id);
        $this->assertEquals('Tên mới', $updatedAuthor->name);

        // Kiểm tra quan hệ với sách mới
        $this->assertCount(3, $updatedAuthor->writtenBooks);
        foreach ($newBookIds as $bookId) {
            $this->assertDatabaseHas('author_book', [
                'author_id' => $author->id,
                'book_id' => $bookId,
            ]);
        }

        // Kiểm tra sách cũ đã bị xóa khỏi quan hệ
        foreach ($oldBooks->pluck('id') as $oldBookId) {
            $this->assertDatabaseMissing('author_book', [
                'author_id' => $author->id,
                'book_id' => $oldBookId,
            ]);
        }
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
