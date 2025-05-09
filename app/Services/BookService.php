<?php

namespace App\Services;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\FindTrashedBookAction;
use App\Actions\Books\RestoreBookAction;
use App\Actions\Books\SyncBookRelationsAction;
use App\Actions\Books\UpdateBookAction;
use App\Actions\Books\ValidateBookAction;
use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\BookDTO;
use App\Http\Filters\V1\BookFilter;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookService extends BaseService
{
    /**
     * Actions
     */
    protected CreateBookAction $createBookAction;

    protected FindTrashedBookAction $findTrashedBookAction;

    protected RestoreBookAction $restoreBookAction;

    protected UpdateBookAction $updateBookAction;

    protected DeleteBookAction $deleteBookAction;

    protected ValidateBookAction $validateBookAction;

    protected SyncBookRelationsAction $syncBookRelationsAction;

    /**
     * BookService constructor.
     */
    public function __construct()
    {
        $this->model = new Book;
        $this->filterClass = BookFilter::class;
        $this->sortClass = BookSort::class;
        $this->with = ['authors', 'genres', 'publisher'];

        // Khởi tạo các action
        $this->createBookAction = new CreateBookAction;
        $this->findTrashedBookAction = new FindTrashedBookAction;
        $this->restoreBookAction = new RestoreBookAction;
        $this->updateBookAction = new UpdateBookAction;
        $this->deleteBookAction = new DeleteBookAction;
        $this->validateBookAction = new ValidateBookAction;
        $this->syncBookRelationsAction = new SyncBookRelationsAction;
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
     * @throws Throwable
     */
    public function createBook(BookDTO $bookDTO): Book
    {
        // Xác thực dữ liệu
        $this->validateBookAction->execute($bookDTO);

        // Kiểm tra sách đã xóa mềm
        $trashedBook = $this->findTrashedBookAction->execute($bookDTO);

        if ($trashedBook) {
            // Khôi phục sách đã xóa
            return $this->restoreBookAction->execute($trashedBook, $bookDTO);
        }

        // Tạo sách mới
        return $this->createBookAction->execute($bookDTO, $this->with);
    }

    /**
     * Lấy thông tin chi tiết sách
     *
     * @throws ModelNotFoundException
     */
    public function getBookById(string|int $bookId): Model
    {
        return $this->getById($bookId);
    }

    /**
     * Cập nhật sách
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     * @throws Throwable
     */
    public function updateBook(string|int $bookId, BookDTO $bookDTO, array $originalRequest = []): Book
    {
        // Tìm sách hiện tại
        $book = Book::findOrFail($bookId);

        // Xác thực dữ liệu cập nhật
        $this->validateBookAction->execute($bookDTO, true, $book);

        // Chuẩn bị dữ liệu cập nhật
        $data = $this->prepareUpdateData($bookDTO);

        // Trích xuất các mối quan hệ từ request ban đầu
        $relations = $this->syncBookRelationsAction->extractRelationsFromRequest($originalRequest);

        // Thực hiện cập nhật
        return $this->updateBookAction->execute($book, $data, $relations, $this->with);
    }

    /**
     * Xóa sách
     *
     * @throws ModelNotFoundException|Exception
     * @throws Throwable
     */
    public function deleteBook(string|int $bookId): Book
    {
        $book = Book::findOrFail($bookId);
        $this->deleteBookAction->execute($book);

        return $book;
    }

    /**
     * Find a trashed resource based on unique attributes
     */
    protected function findTrashed(BaseDTO $dto): ?Model
    {
        // Đảm bảo DTO là kiểu BookDTO trước khi tiếp tục
        if (! ($dto instanceof BookDTO) || ! isset($dto->title) || ! isset($dto->edition)) {
            return null;
        }

        return $this->findTrashedBookAction->execute($dto);
    }

    /**
     * Actions to perform before deleting a resource
     */
    protected function beforeDelete(Model $resource): void
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
     */
    protected function returnDeletedResource(): bool
    {
        return true;
    }

    /**
     * Chuẩn bị dữ liệu cập nhật từ BookDTO
     */
    private function prepareUpdateData(BookDTO $bookDTO): array
    {
        $data = [];

        // Chỉ bao gồm các trường có giá trị được thiết lập
        if (isset($bookDTO->title)) {
            $data['title'] = $bookDTO->title;
        }

        if (isset($bookDTO->edition)) {
            $data['edition'] = $bookDTO->edition;
        }

        if (isset($bookDTO->price)) {
            $data['price'] = $bookDTO->price;
        }

        // Chỉ cập nhật sold_count khi giá trị được gửi trong request
        if ($bookDTO->sold_count !== null) {
            $data['sold_count'] = $bookDTO->sold_count;
        }

        // Chỉ cập nhật available_count khi giá trị được gửi trong request
        if ($bookDTO->available_count !== null) {
            $data['available_count'] = $bookDTO->available_count;
        }

        if (isset($bookDTO->description)) {
            $data['description'] = $bookDTO->description;
        }

        if (isset($bookDTO->publication_date)) {
            $data['publication_date'] = $bookDTO->publication_date;
        }

        if (isset($bookDTO->image_url)) {
            $data['image_url'] = $bookDTO->image_url;
        }

        if (isset($bookDTO->pages)) {
            $data['pages'] = $bookDTO->pages;
        }

        if (isset($bookDTO->publisher_id)) {
            $data['publisher_id'] = $bookDTO->publisher_id;
        }

        return $data;
    }
}
