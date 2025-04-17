<?php

namespace Tests\Unit\Services;

use App\DTOs\Book\BookDTO;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Services\BookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookService $bookService;

    private Publisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookService = new BookService();
        $this->publisher = Publisher::factory()->create();
    }

    public function test_it_can_get_all_books()
    {
        // Tạo một số sách
        Book::factory()->count(5)->create([
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo request giả lập
        $request = new Request();

        // Gọi method getAllBooks
        $result = $this->bookService->getAllBooks($request, 10);

        // Kiểm tra kết quả
        $this->assertEquals(5, $result->count());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_it_can_get_book_by_id()
    {
        // Tạo một sách
        $book = Book::factory()->create([
          'publisher_id' => $this->publisher->id,
        ]);

        // Gọi method getBookById
        $result = $this->bookService->getBookById($book->id);

        // Kiểm tra kết quả
        $this->assertEquals($book->id, $result->id);
        $this->assertEquals($book->title, $result->title);
    }

    public function test_it_throws_exception_when_book_not_found()
    {
        // Dự kiến lỗi ModelNotFoundException khi tìm sách không tồn tại
        $this->expectException(ModelNotFoundException::class);

        // Gọi method getBookById với ID không tồn tại
        $this->bookService->getBookById('non-existent-id');
    }

    public function test_it_can_create_book()
    {
        // Tạo tác giả và thể loại
        $authors = Author::factory()->count(2)->create();
        $genres = Genre::factory()->count(2)->create();

        // Tạo BookDTO
        $bookData = [
          'title' => 'Sách Test',
          'description' => 'Mô tả sách test',
          'price' => 150000,
          'edition' => 1,
          'pages' => 200,
          'publication_date' => '2023-01-01',
          'image_url' => 'https://example.com/book.jpg',
          'publisher_id' => $this->publisher->id,
          'author_ids' => $authors->pluck('id')->toArray(),
          'genre_ids' => $genres->pluck('id')->toArray(),
        ];

        $bookDTO = new BookDTO(
            $bookData['title'],
            $bookData['description'],
            $bookData['price'],
            $bookData['edition'],
            $bookData['pages'],
            $bookData['image_url'],
            $bookData['publication_date'],
            $bookData['publisher_id'],
            $bookData['author_ids'],
            $bookData['genre_ids']
        );

        // Gọi method createBook
        $result = $this->bookService->createBook($bookDTO);

        // Kiểm tra kết quả
        $this->assertNotNull($result->id);
        $this->assertEquals('Sách Test', $result->title);
        $this->assertEquals(150000, $result->price);
        $this->assertEquals(1, $result->edition);
        $this->assertEquals($this->publisher->id, $result->publisher_id);

        // Kiểm tra quan hệ với tác giả và thể loại
        $this->assertCount(2, $result->authors);
        $this->assertCount(2, $result->genres);

        // Kiểm tra database
        $this->assertDatabaseHas('books', [
          'title' => 'Sách Test',
          'description' => 'Mô tả sách test',
        ]);
    }

    public function test_it_restores_soft_deleted_book_with_same_title_and_edition()
    {
        // Tạo một sách và soft delete nó
        $book = Book::factory()->create([
          'title' => 'Sách Đã Xóa',
          'edition' => 2,
          'publisher_id' => $this->publisher->id,
        ]);
        $bookId = $book->id;
        $book->delete();

        // Kiểm tra rằng sách đã bị soft delete
        $this->assertSoftDeleted('books', [
          'id' => $bookId,
        ]);

        // Tạo BookDTO với title và edition giống sách đã xóa
        $bookDTO = new BookDTO(
            'Sách Đã Xóa',
            'Mô tả mới',
            200000,
            2,
            250,
            'https://example.com/new-image.jpg',
            '2023-02-01',
            $this->publisher->id
        );

        // Gọi method createBook
        $result = $this->bookService->createBook($bookDTO);

        // Kiểm tra kết quả
        $this->assertEquals($bookId, $result->id); // ID phải giống sách ban đầu vì đã restore
        $this->assertEquals('Sách Đã Xóa', $result->title);
        $this->assertEquals('Mô tả mới', $result->description); // Mô tả đã được cập nhật
        $this->assertEquals(2, $result->edition);

        // Kiểm tra database rằng sách không còn bị soft delete
        $this->assertDatabaseHas('books', [
          'id' => $bookId,
          'deleted_at' => null,
        ]);
    }

    public function test_it_can_update_book()
    {
        // Tạo một sách
        $book = Book::factory()->create([
          'title' => 'Sách Gốc',
          'description' => 'Mô tả gốc',
          'price' => 150000,
          'edition' => 1,
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo BookDTO cho cập nhật
        $bookDTO = new BookDTO(
            'Sách Đã Cập Nhật',
            'Mô tả đã cập nhật',
            null,
            null,
            null,
            null,
            null,
            null
        );

        // Gọi method updateBook
        $result = $this->bookService->updateBook($book->id, $bookDTO);

        // Kiểm tra kết quả
        $this->assertEquals($book->id, $result->id);
        $this->assertEquals('Sách Đã Cập Nhật', $result->title);
        $this->assertEquals('Mô tả đã cập nhật', $result->description);
        $this->assertEquals(150000, $result->price); // Giá không thay đổi vì không cập nhật
        $this->assertEquals(1, $result->edition); // Phiên bản không thay đổi vì không cập nhật

        // Kiểm tra database
        $this->assertDatabaseHas('books', [
          'id' => $book->id,
          'title' => 'Sách Đã Cập Nhật',
          'description' => 'Mô tả đã cập nhật',
        ]);
    }

    public function test_it_throws_validation_exception_when_updating_to_existing_title_edition_combination()
    {
        // Tạo hai sách với title và edition khác nhau
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

        // Tạo BookDTO cập nhật book2 thành title và edition của book1
        $bookDTO = new BookDTO(
            'Sách thứ nhất', // Title giống book1
            'Mô tả mới',
            null,
            1, // Edition giống book1
            null,
            null,
            null,
            null
        );

        // Dự kiến lỗi ValidationException
        $this->expectException(ValidationException::class);

        // Gọi method updateBook và kỳ vọng ngoại lệ
        $this->bookService->updateBook($book2->id, $bookDTO);
    }

    public function test_it_can_delete_book()
    {
        // Tạo một sách
        $book = Book::factory()->create([
          'publisher_id' => $this->publisher->id,
        ]);

        // Gọi method deleteBook
        $result = $this->bookService->deleteBook($book->id);

        // Kiểm tra kết quả
        $this->assertInstanceOf(Book::class, $result);
        $this->assertEquals($book->id, $result->id);

        // Kiểm tra database rằng sách đã bị soft delete
        $this->assertSoftDeleted('books', [
          'id' => $book->id,
        ]);
    }

    public function test_it_can_filter_books_by_title()
    {
        // Tạo các sách với tiêu đề khác nhau
        Book::factory()->create([
          'title' => 'PHP Master',
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Laravel Advanced',
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'PHP Beginner',
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo request với filter theo tiêu đề
        $request = new Request(['filter' => ['title' => 'PHP']]);

        // Lấy danh sách sách theo filter
        $filteredBooks = $this->bookService->getAllBooks($request);

        // Kiểm tra kết quả
        $this->assertEquals(2, $filteredBooks->count());
        $this->assertStringContainsString('PHP', $filteredBooks[0]->title);
        $this->assertStringContainsString('PHP', $filteredBooks[1]->title);
    }

    public function test_it_can_filter_books_by_price_range()
    {
        // Tạo các sách với giá khác nhau
        Book::factory()->create([
          'title' => 'Book 1',
          'price' => 250000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Book 2',
          'price' => 350000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Book 3',
          'price' => 450000,
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo request với filter theo khoảng giá - sử dụng tên tham số đúng
        $request = new Request(['filter' => ['price_from' => 300000, 'price_to' => 400000]]);

        // Lấy danh sách sách theo filter
        $filteredBooks = $this->bookService->getAllBooks($request);

        // Kiểm tra kết quả
        $this->assertEquals(1, $filteredBooks->count());
        $this->assertEquals(350000, $filteredBooks[0]->price);
    }

    public function test_it_can_sort_books_by_price_ascending()
    {
        // Tạo các sách với giá khác nhau
        Book::factory()->create([
          'title' => 'Expensive Book',
          'price' => 450000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Cheap Book',
          'price' => 250000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Medium Book',
          'price' => 350000,
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo request với sort theo giá tăng dần
        $request = new Request(['sort' => 'price']);

        // Lấy danh sách sách theo sort
        $sortedBooks = $this->bookService->getAllBooks($request);

        // Kiểm tra kết quả
        $this->assertEquals(250000, $sortedBooks[0]->price);
        $this->assertEquals(350000, $sortedBooks[1]->price);
        $this->assertEquals(450000, $sortedBooks[2]->price);
    }

    public function test_it_can_sort_books_by_price_descending()
    {
        // Tạo các sách với giá khác nhau
        Book::factory()->create([
          'title' => 'Expensive Book',
          'price' => 450000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Cheap Book',
          'price' => 250000,
          'publisher_id' => $this->publisher->id,
        ]);

        Book::factory()->create([
          'title' => 'Medium Book',
          'price' => 350000,
          'publisher_id' => $this->publisher->id,
        ]);

        // Tạo request với sort theo giá giảm dần
        $request = new Request(['sort' => '-price']);

        // Lấy danh sách sách theo sort
        $sortedBooks = $this->bookService->getAllBooks($request);

        // Kiểm tra kết quả
        $this->assertEquals(450000, $sortedBooks[0]->price);
        $this->assertEquals(350000, $sortedBooks[1]->price);
        $this->assertEquals(250000, $sortedBooks[2]->price);
    }

    public function test_it_can_update_book_with_partial_relationships()
    {
        // Tạo một sách với tác giả và thể loại
        $book = Book::factory()->create([
          'publisher_id' => $this->publisher->id,
        ]);

        $initialAuthors = Author::factory()->count(2)->create();
        $initialGenres = Genre::factory()->count(2)->create();

        $book->authors()->attach($initialAuthors->pluck('id'));
        $book->genres()->attach($initialGenres->pluck('id'));

        // Tạo tác giả mới
        $newAuthors = Author::factory()->count(1)->create();

        // Tạo BookDTO chỉ cập nhật tác giả
        $bookDTO = new BookDTO(
            null, // title
            null, // description
            null, // price
            null, // edition
            null, // pages
            null, // image_url
            null, // publication_date
            null, // publisher_id
            $newAuthors->pluck('id')->toArray(), // author_ids
            [] // genre_ids (empty means don't change)
        );

        // Cập nhật sách
        $validatedData = [
          'data' => [
            'relationships' => [
              'authors' => [
                'data' => $newAuthors->map(function ($author) {
                    return ['id' => $author->id, 'type' => 'authors'];
                })->toArray(),
              ],
            ],
          ],
        ];

        $updatedBook = $this->bookService->updateBook($book->id, $bookDTO, $validatedData);

        // Kiểm tra kết quả
        $this->assertCount(1, $updatedBook->authors);
        $this->assertEquals($newAuthors[0]->id, $updatedBook->authors[0]->id);

        // Thể loại không thay đổi vì chúng ta không chỉ định nó trong validatedData
        $this->assertCount(2, $updatedBook->genres);
    }

    public function test_it_can_change_book_publisher()
    {
        // Tạo một sách và hai nhà xuất bản
        $book = Book::factory()->create([
          'publisher_id' => $this->publisher->id,
        ]);

        $newPublisher = Publisher::factory()->create();

        // Tạo BookDTO với publisher_id đặt đúng
        $bookDTO = new BookDTO(
            null, // title
            null, // description
            null, // price
            null, // edition
            null, // pages
            null, // image_url
            null, // publication_date
            $newPublisher->id // publisher_id
        );

        // Dữ liệu từ request với định dạng đúng
        $validatedData = [
          'data' => [
            'attributes' => [
              // Empty attributes but required to trigger the publisher update
            ],
            'relationships' => [
              'publisher' => [
                'id' => $newPublisher->id,
              ],
            ],
          ],
        ];

        $updatedBook = $this->bookService->updateBook($book->id, $bookDTO, $validatedData);

        // Kiểm tra publisher_id đã được cập nhật trong database
        $this->assertDatabaseHas('books', [
          'id' => $book->id,
          'publisher_id' => $newPublisher->id,
        ]);

        // Kiểm tra kết quả trả về
        $this->assertEquals($newPublisher->id, $updatedBook->publisher_id);
    }
}
