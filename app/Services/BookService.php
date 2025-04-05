<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
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

class BookService
{
    /**
     * Lấy danh sách sách với filter và sort
     */
    public function getAllBooks(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $query = Book::query();

        // Apply filters
        $bookFilter = new BookFilter($request);
        $query = $bookFilter->apply($query);

        // Apply sorting
        $bookSort = new BookSort($request);
        $query = $bookSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
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

        // Kiểm tra xem có sách nào (chưa bị xóa) với cùng title và edition hay không
        $existingBook = Book::where('title', $data['title'])
          ->where('edition', $data['edition'])
          ->first();

        if ($existingBook) {
            throw ValidationException::withMessages([
              'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.'],
            ]);
        }

        // Kiểm tra xem có sách nào đã bị xóa mềm với cùng title và edition
        $deletedBook = Book::withTrashed()
          ->where('title', $data['title'])
          ->where('edition', $data['edition'])
          ->onlyTrashed() // Chỉ lấy các sách đã bị xóa
          ->first();

        // Nếu tồn tại, khôi phục và cập nhật
        if ($deletedBook) {
            try {
                DB::beginTransaction();

                // Khôi phục sách
                $deletedBook->restore();

                // Cập nhật thông tin mới
                $deletedBook->update($data);

                // Cập nhật quan hệ authors nếu có
                if (! empty($bookDTO->author_ids)) {
                    $deletedBook->authors()->sync($bookDTO->author_ids);
                }

                // Cập nhật quan hệ genres nếu có
                if (! empty($bookDTO->genre_ids)) {
                    $deletedBook->genres()->sync($bookDTO->genre_ids);
                }

                DB::commit();

                return $deletedBook->fresh(['authors', 'genres', 'publisher']);
            } catch (Exception $e) {
                DB::rollBack();

                throw $e;
            }
        }

        // Tạo sách mới nếu không tìm thấy sách đã xóa với cùng title và edition
        try {
            DB::beginTransaction();

            // Tạo sách
            $book = Book::create($data);

            // Gán tác giả
            if (! empty($bookDTO->author_ids)) {
                $book->authors()->attach($bookDTO->author_ids);
            }

            // Gán thể loại
            if (! empty($bookDTO->genre_ids)) {
                $book->genres()->attach($bookDTO->genre_ids);
            }

            DB::commit();

            return $book->fresh(['authors', 'genres', 'publisher']);
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
        return Book::with(['authors', 'genres', 'publisher'])->findOrFail($bookId);
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

            // Chỉ cập nhật khi có dữ liệu
            if (! empty($data)) {
                DB::beginTransaction();

                // Nếu đang cập nhật title mà không cập nhật edition hoặc ngược lại,
                // có thể gây lỗi unique constraint. Kiểm tra trường hợp này.
                if (isset($data['title']) && ! isset($data['edition'])) {
                    // Thêm edition hiện tại vào dữ liệu cập nhật để tránh lỗi unique
                    $data['edition'] = $oldEdition;

                    // Kiểm tra xem có sách nào khác với title mới và edition hiện tại không
                    $existingBook = Book::where('title', $data['title'])
                      ->where('edition', $oldEdition)
                      ->where('id', '!=', $bookId)
                      ->first();

                    if ($existingBook) {
                        DB::rollBack();

                        throw ValidationException::withMessages([
                          'data.attributes.title' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề khác hoặc cập nhật cả phiên bản.'],
                        ]);
                    }
                } elseif (isset($data['edition']) && ! isset($data['title'])) {
                    // Thêm title hiện tại vào dữ liệu cập nhật để tránh lỗi unique
                    $data['title'] = $oldTitle;

                    // Kiểm tra xem có sách nào khác với title hiện tại và edition mới không
                    $existingBook = Book::where('title', $oldTitle)
                      ->where('edition', $data['edition'])
                      ->where('id', '!=', $bookId)
                      ->first();

                    if ($existingBook) {
                        DB::rollBack();

                        throw ValidationException::withMessages([
                          'data.attributes.edition' => ['Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng phiên bản khác hoặc cập nhật cả tiêu đề.'],
                        ]);
                    }
                }

                // Cập nhật sách
                $book->update($data);

                // Kiểm tra xem relationships authors có trong request gốc hay không
                $hasAuthorsInRequest = isset($originalRequest['data']['relationships']['authors']);

                // Chỉ đồng bộ tác giả khi relationships authors có trong request
                if ($hasAuthorsInRequest) {
                    $book->authors()->sync($bookDTO->author_ids);
                }

                // Kiểm tra xem relationships genres có trong request gốc hay không
                $hasGenresInRequest = isset($originalRequest['data']['relationships']['genres']);

                // Chỉ đồng bộ thể loại khi relationships genres có trong request
                if ($hasGenresInRequest) {
                    $book->genres()->sync($bookDTO->genre_ids);
                }

                DB::commit();

                return $book->fresh(['authors', 'genres', 'publisher']);
            }

            return $book->fresh(['authors', 'genres', 'publisher']);
        } catch (ValidationException $e) {
            // ValidationException sẽ được ném lên cho controller xử lý
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
            if (isset($book)) {
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
        try {
            DB::beginTransaction();

            $book = Book::findOrFail($bookId);

            // Xóa các mối quan hệ discount liên quan đến sách này
            $book->getAllActiveDiscounts()->each(function ($discount) use ($book) {
                $discount->targets()->where('target_id', $book->id)->delete();
            });

            // Soft delete sách
            $book->delete();

            DB::commit();

            return $book;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
