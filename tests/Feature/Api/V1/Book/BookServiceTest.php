<?php

namespace Tests\Unit\Services;

use App\DTOs\Book\BookDTO;
use App\Models\Author;
use App\Models\Book;
use App\Models\Discount;
use App\Models\DiscountTarget;
use App\Models\Genre;
use App\Models\Publisher;
use App\Services\BookService;
use Carbon\Carbon;
use Exception;
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

  public function test_it_validates_book_creation_with_invalid_data()
  {
    // Tạo BookDTO thiếu title (bắt buộc)
    $bookDTO = new BookDTO(
      '', // title trống (không hợp lệ)
      'Mô tả sách test',
      150000,
      1,
      200,
      'https://example.com/book.jpg',
      '2023-01-01',
      $this->publisher->id
    );

    // Dự kiến lỗi ValidationException
    $this->expectException(ValidationException::class);

    // Gọi method createBook và kỳ vọng ngoại lệ
    $this->bookService->createBook($bookDTO);
  }

  public function test_it_properly_syncs_relationships_during_update()
  {
    // Tạo một sách với tác giả và thể loại
    $book = Book::factory()->create([
      'publisher_id' => $this->publisher->id,
    ]);

    $initialAuthors = Author::factory()->count(3)->create();
    $initialGenres = Genre::factory()->count(3)->create();

    $book->authors()->attach($initialAuthors->pluck('id'));
    $book->genres()->attach($initialGenres->pluck('id'));

    // Tạo tác giả và thể loại mới
    $newAuthors = Author::factory()->count(2)->create();
    $newGenres = Genre::factory()->count(2)->create();

    // Tạo BookDTO với quan hệ mới
    $bookDTO = new BookDTO(
      'Sách Đã Cập Nhật',
      'Mô tả đã cập nhật',
      200000,
      2,
      300,
      'https://example.com/updated.jpg',
      '2023-05-01',
      $this->publisher->id,
      $newAuthors->pluck('id')->toArray(),
      $newGenres->pluck('id')->toArray()
    );

    // Dữ liệu từ request với định dạng đúng
    $validatedData = [
      'data' => [
        'relationships' => [
          'authors' => [
            'data' => $newAuthors->map(function ($author) {
              return ['id' => $author->id, 'type' => 'authors'];
            })->toArray(),
          ],
          'genres' => [
            'data' => $newGenres->map(function ($genre) {
              return ['id' => $genre->id, 'type' => 'genres'];
            })->toArray(),
          ]
        ],
      ],
    ];

    // Cập nhật sách
    $updatedBook = $this->bookService->updateBook($book->id, $bookDTO, $validatedData);

    // Kiểm tra kết quả
    $this->assertEquals('Sách Đã Cập Nhật', $updatedBook->title);
    $this->assertEquals(200000, $updatedBook->price);
    $this->assertEquals(2, $updatedBook->edition);

    // Kiểm tra các mối quan hệ đã được cập nhật
    $this->assertCount(2, $updatedBook->authors);
    $this->assertCount(2, $updatedBook->genres);

    // Kiểm tra rằng các quan hệ cũ đã bị thay thế
    foreach ($initialAuthors as $author) {
      $this->assertFalse($updatedBook->authors->contains($author->id));
    }

    foreach ($initialGenres as $genre) {
      $this->assertFalse($updatedBook->genres->contains($genre->id));
    }

    // Kiểm tra rằng các quan hệ mới đã được thêm
    foreach ($newAuthors as $author) {
      $this->assertTrue($updatedBook->authors->contains($author->id));
    }

    foreach ($newGenres as $genre) {
      $this->assertTrue($updatedBook->genres->contains($genre->id));
    }
  }

  public function test_it_deletes_associated_discounts_when_deleting_book()
  {
    // Tạo một sách
    $book = Book::factory()->create([
      'publisher_id' => $this->publisher->id,
    ]);

    // Tạo discount và liên kết với sách
    $discount = Discount::factory()->create([
      'start_date' => Carbon::now()->subDay(),
      'end_date' => Carbon::now()->addDay(),
    ]);

    $discountTarget = DiscountTarget::create([
      'discount_id' => $discount->id,
      'target_id' => $book->id,
      'target_type' => Book::class,
    ]);

    // Kiểm tra discount đã được tạo
    $this->assertDatabaseHas('discount_targets', [
      'discount_id' => $discount->id,
      'target_id' => $book->id,
    ]);

    // Xóa sách
    $this->bookService->deleteBook($book->id);

    // Kiểm tra discountTarget đã bị xóa
    $this->assertDatabaseMissing('discount_targets', [
      'discount_id' => $discount->id,
      'target_id' => $book->id,
    ]);

    // Kiểm tra sách đã bị soft delete
    $this->assertSoftDeleted('books', [
      'id' => $book->id,
    ]);
  }

  public function test_it_handles_empty_relationships_correctly()
  {
    // Tạo một sách không có tác giả hay thể loại
    $bookDTO = new BookDTO(
      'Sách Không Có Quan Hệ',
      'Mô tả sách',
      150000,
      1,
      200,
      'https://example.com/book.jpg',
      '2023-01-01',
      $this->publisher->id,
      [], // Mảng rỗng cho author_ids
      []  // Mảng rỗng cho genre_ids
    );

    // Tạo sách
    $book = $this->bookService->createBook($bookDTO);

    // Kiểm tra kết quả
    $this->assertNotNull($book->id);
    $this->assertEquals('Sách Không Có Quan Hệ', $book->title);
    $this->assertEquals(150000, $book->price);

    // Kiểm tra các mối quan hệ
    $this->assertCount(0, $book->authors);
    $this->assertCount(0, $book->genres);
  }

  public function test_it_handles_transaction_rollback_on_exception()
  {
    // Mock BookService với phương thức bị lỗi
    $mockService = $this->getMockBuilder(BookService::class)
      ->onlyMethods(['createBook'])
      ->getMock();

    // Thiết lập mock để ném ngoại lệ khi gọi createBook
    $mockService->method('createBook')
      ->will($this->throwException(new Exception('Simulated database error')));

    // Tạo BookDTO hợp lệ
    $bookDTO = new BookDTO(
      'Sách Test Transaction',
      'Mô tả sách',
      150000,
      1,
      200,
      'https://example.com/book.jpg',
      '2023-01-01',
      $this->publisher->id
    );

    // Dự kiến lỗi Exception
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Simulated database error');

    // Gọi phương thức createBook và kỳ vọng nó sẽ ném ngoại lệ
    $mockService->createBook($bookDTO);

    // Kiểm tra không có sách nào được lưu vào database
    $this->assertDatabaseMissing('books', [
      'title' => 'Sách Test Transaction'
    ]);
  }

  public function test_it_can_update_book_without_changing_relationships()
  {
    // Tạo một sách với tác giả và thể loại
    $book = Book::factory()->create([
      'title' => 'Sách Gốc',
      'description' => 'Mô tả gốc',
      'price' => 150000,
      'edition' => 1,
      'publisher_id' => $this->publisher->id,
    ]);

    $authors = Author::factory()->count(2)->create();
    $genres = Genre::factory()->count(2)->create();

    $book->authors()->attach($authors->pluck('id'));
    $book->genres()->attach($genres->pluck('id'));

    // Tạo BookDTO chỉ cập nhật thuộc tính, không thay đổi quan hệ
    $bookDTO = new BookDTO(
      'Sách Đã Cập Nhật',
      'Mô tả đã cập nhật',
      200000,
      2,
      null,
      null,
      null,
      null,
      [], // mảng trống thay vì null cho author_ids
      []  // mảng trống thay vì null cho genre_ids
    );

    // Dữ liệu từ request không chứa thông tin về relationships
    $validatedData = [
      'data' => [
        'attributes' => [
          'title' => 'Sách Đã Cập Nhật',
          'description' => 'Mô tả đã cập nhật',
          'price' => 200000,
          'edition' => 2,
        ],
      ],
    ];

    // Cập nhật sách
    $updatedBook = $this->bookService->updateBook($book->id, $bookDTO, $validatedData);

    // Kiểm tra kết quả
    $this->assertEquals('Sách Đã Cập Nhật', $updatedBook->title);
    $this->assertEquals('Mô tả đã cập nhật', $updatedBook->description);
    $this->assertEquals(200000, $updatedBook->price);
    $this->assertEquals(2, $updatedBook->edition);

    // Kiểm tra các mối quan hệ không thay đổi
    $this->assertCount(2, $updatedBook->authors);
    $this->assertCount(2, $updatedBook->genres);

    foreach ($authors as $author) {
      $this->assertTrue($updatedBook->authors->contains($author->id));
    }

    foreach ($genres as $genre) {
      $this->assertTrue($updatedBook->genres->contains($genre->id));
    }
  }
}
