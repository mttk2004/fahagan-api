<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\Book\BookDTO;
use App\Filters\BookFilter;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookService extends BaseService
{
  /**
   * BookService constructor.
   */
  public function __construct()
  {
    $this->model = new Book();
    $this->filterClass = BookFilter::class;
    $this->sortClass = BookSort::class;
    $this->with = ['authors', 'genres', 'publisher'];
  }

  /**
   * Lấy danh sách sách với filter và sort
   */
  public function getAllBooks(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
  {
    return $this->getAll($request, $perPage);
  }

  /**
   * Tạo sách mới hoặc khôi phục sách đã bị xóa mềm nếu đã tồn tại với cùng title và edition
   *
   * @throws ValidationException
   * @throws Exception
   */
  public function createBook(BookDTO $bookDTO): Book
  {
    $data = $bookDTO->toArray();

    // Kiểm tra xem có đủ thông tin cần thiết hay không
    if (! isset($data['title']) || ! isset($data['edition'])) {
      throw ValidationException::withMessages([
        'data.attributes.title' => ['Tiêu đề sách là bắt buộc.'],
        'data.attributes.edition' => ['Phiên bản sách là bắt buộc.'],
      ]);
    }

    // Kiểm tra xem có sách nào đã bị xóa mềm với cùng title và edition hay không
    $trashedBook = Book::withTrashed()
      ->where('title', $data['title'])
      ->where('edition', $data['edition'])
      ->onlyTrashed()
      ->first();

    if ($trashedBook) {
      try {
        DB::beginTransaction();

        // Khôi phục sách đã bị xóa mềm
        $trashedBook->restore();

        // Cập nhật thông tin từ DTO
        $trashedBook->update($data);

        // Xử lý relations nếu có
        $relations = [];
        if (! empty($bookDTO->author_ids)) {
          $relations['authors'] = $bookDTO->author_ids;
        }
        if (! empty($bookDTO->genre_ids)) {
          $relations['genres'] = $bookDTO->genre_ids;
        }

        if (! empty($relations)) {
          $this->syncRelations($trashedBook, $relations);
        }

        DB::commit();

        return $trashedBook->fresh($this->with);
      } catch (Exception $e) {
        DB::rollBack();

        throw $e;
      }
    }

    // Kiểm tra xem có sách nào (chưa bị xóa) với cùng title và edition hay không
    $existingBook = Book::where('title', $data['title'])
      ->where('edition', $data['edition'])
      ->first();

    if ($existingBook) {
      throw ValidationException::withMessages([
        'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.'],
      ]);
    }

    // Tạo sách với relations
    $relations = [];
    if (! empty($bookDTO->author_ids)) {
      $relations['authors'] = $bookDTO->author_ids;
    }
    if (! empty($bookDTO->genre_ids)) {
      $relations['genres'] = $bookDTO->genre_ids;
    }

    try {
      // Tạo sách mới - tránh sử dụng chuỗi phương thức với tap() vì nó trả về Builder không phải Model
      DB::beginTransaction();

      // Đầu tiên tạo Book model
      $book = $this->model::create($data);

      // Sau đó đồng bộ các mối quan hệ nếu có
      if (! empty($relations)) {
        $this->syncRelations($book, $relations);
      }

      DB::commit();

      // Trả về book với eager loaded relations
      return $book->fresh($this->with);
    } catch (QueryException $e) {
      DB::rollBack();
      // Nếu là lỗi ràng buộc duy nhất, chuyển nó thành ValidationException
      if ($e->getCode() == 23000 && strpos($e->getMessage(), 'books_title_edition_unique') !== false) {
        throw ValidationException::withMessages([
          'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.'],
        ]);
      }

      throw $e;
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }

  /**
   * Lấy thông tin chi tiết sách
   *
   * @throws ModelNotFoundException
   */
  public function getBookById(string|int $bookId): Book
  {
    return $this->getById($bookId);
  }

  /**
   * Cập nhật sách
   *
   * @throws ModelNotFoundException
   * @throws ValidationException
   * @throws Exception
   */
  public function updateBook(string|int $bookId, BookDTO $bookDTO, array $originalRequest = []): Book
  {
    try {
      // Tìm sách hiện tại
      $book = Book::findOrFail($bookId);

      // Lấy dữ liệu cập nhật từ DTO
      $data = $bookDTO->toArray();

      // Xử lý publisher_id nếu có trong dữ liệu gốc
      if (isset($originalRequest['data']['relationships']['publisher']['id'])) {
        $data['publisher_id'] = $originalRequest['data']['relationships']['publisher']['id'];
      }

      // Lưu thuộc tính cũ trước khi cập nhật
      $oldTitle = $book->title;
      $oldEdition = $book->edition;

      // Nếu đang cập nhật cả title và edition, kiểm tra xem có sách nào khác
      // với cùng title và edition hay không
      if (
        isset($data['title']) && isset($data['edition']) &&
        ($data['title'] !== $oldTitle || $data['edition'] !== $oldEdition)
      ) {
        $existingBook = Book::where('title', $data['title'])
          ->where('edition', $data['edition'])
          ->where('id', '!=', $bookId)
          ->first();

        if ($existingBook) {
          throw ValidationException::withMessages([
            'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.'],
          ]);
        }
      }

      // Chuẩn bị relations
      $relations = [];

      // Kiểm tra xem relationships authors có trong request gốc hay không
      $hasAuthorsInRequest = isset($originalRequest['data']['relationships']['authors']);
      if ($hasAuthorsInRequest) {
        $authorIds = [];
        if (isset($originalRequest['data']['relationships']['authors']['data'])) {
          $authorIds = collect($originalRequest['data']['relationships']['authors']['data'])
            ->pluck('id')
            ->toArray();
        }
        $relations['authors'] = $authorIds;
      }

      // Kiểm tra xem relationships genres có trong request gốc hay không
      $hasGenresInRequest = isset($originalRequest['data']['relationships']['genres']);
      if ($hasGenresInRequest) {
        $genreIds = [];
        if (isset($originalRequest['data']['relationships']['genres']['data'])) {
          $genreIds = collect($originalRequest['data']['relationships']['genres']['data'])
            ->pluck('id')
            ->toArray();
        }
        $relations['genres'] = $genreIds;
      }

      try {
        DB::beginTransaction();

        // Cập nhật thông tin sách
        $book->update($data);

        // Cập nhật mối quan hệ nếu chúng được gửi trong request ban đầu
        if (isset($originalRequest['data']['relationships'])) {
          $this->syncRelations($book, $relations);
        }

        DB::commit();

        return $book->fresh($this->with);
      } catch (Exception $e) {
        DB::rollBack();
        throw $e;
      }
    } catch (ValidationException $e) {
      throw $e;
    } catch (QueryException $e) {
      // Nếu là lỗi ràng buộc duy nhất, chuyển nó thành ValidationException
      if ($e->getCode() == 23000 && strpos($e->getMessage(), 'books_title_edition_unique') !== false) {
        throw ValidationException::withMessages([
          'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.'],
        ]);
      }

      throw $e;
    } catch (Exception $e) {
      if (isset($book) && DB::transactionLevel() > 0) {
        // Rollback transaction nếu có
        DB::rollBack();
      }

      throw $e;
    }
  }

  /**
   * Xóa sách
   *
   * @throws ModelNotFoundException
   */
  public function deleteBook(string|int $bookId): Book
  {
    $book = $this->delete($bookId);

    return $book ?? $this->getById($bookId);
  }

  /**
   * Find a trashed resource based on unique attributes
   *
   * @param BaseDTO $dto
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  protected function findTrashed(BaseDTO $dto): ?\Illuminate\Database\Eloquent\Model
  {
    // Đảm bảo DTO là kiểu BookDTO trước khi tiếp tục
    if (! ($dto instanceof BookDTO) || ! isset($dto->title) || ! isset($dto->edition)) {
      return null;
    }

    return Book::withTrashed()
      ->where('title', $dto->title)
      ->where('edition', $dto->edition)
      ->onlyTrashed()
      ->first();
  }

  /**
   * Actions to perform before deleting a resource
   *
   * @param \Illuminate\Database\Eloquent\Model $resource
   * @return void
   */
  protected function beforeDelete(\Illuminate\Database\Eloquent\Model $resource): void
  {
    // Đảm bảo tài nguyên là đối tượng Book trước khi tiếp tục
    if ($resource instanceof Book) {
      // Xóa các mối quan hệ discount liên quan đến sách này
      $resource->getAllActiveDiscounts()->each(function ($discount) use ($resource) {
        $discount->targets()->where('target_id', $resource->id)->delete();
      });
    }
  }

  /**
   * Whether to return the deleted resource
   *
   * @return bool
   */
  protected function returnDeletedResource(): bool
  {
    return true;
  }
}
