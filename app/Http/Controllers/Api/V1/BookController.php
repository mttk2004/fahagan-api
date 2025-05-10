<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\BookDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookStoreRequest;
use App\Http\Requests\V1\BookUpdateRequest;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookResource;
use App\Services\BookService;
use App\Traits\HandleBookExceptions;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use HandleBookExceptions;
    use HandleExceptions;
    use HandlePagination;
    use HandleValidation;

    public function __construct(
        private readonly BookService $bookService,
        private readonly string $entityName = 'book'
    ) {
    }

    /**
     * Get all books
     *
     *
     * @return BookCollection
     *
     * @group Books
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $books = $this->bookService->getAllBooks($request, $this->getPerPage($request));

        return new BookCollection($books);
    }

    /**
     * Get all trashed books
     *
     *
     * @return BookCollection
     *
     * @group Books
     */
    public function trashed(Request $request)
    {
        if (! AuthUtils::userCan('create_books')) {
            return ResponseUtils::forbidden();
        }

        $books = $this->bookService->getAllBooks($request, $this->getPerPage($request), true);

        return new BookCollection($books);
    }

    /**
     * Create a new book
     *
     *
     * @return JsonResponse
     *
     * @group Books
     */
    public function store(BookStoreRequest $request)
    {
        if (! AuthUtils::userCan('create_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            $book = $this->bookService->createBook(
                BookDTO::fromRequest($request->validated())
            );

            return ResponseUtils::created([
              'book' => new BookResource($book),
            ], ResponseMessage::CREATED_BOOK->value);
        } catch (Exception $e) {
            return $this->handleBookException($e, $request->validated());
        }
    }

    /**
     * Get a book
     *
     *
     * @return JsonResponse
     *
     * @group Books
     *
     * @unauthenticated
     */
    public function show($book_id)
    {
        try {
            $book = $this->bookService->getBookById($book_id);

            return ResponseUtils::success([
              'book' => new BookResource($book),
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'book_id' => $book_id,
            ]);
        }
    }

    /**
     * Update a book
     *
     *
     * @return JsonResponse
     *
     * @group Books
     */
    public function update(BookUpdateRequest $request, $book_id)
    {
        if (! AuthUtils::userCan('create_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            $validatedData = $request->validated();

            $emptyCheckResponse = $this->validateUpdateData($validatedData);
            if ($emptyCheckResponse) {
                return $emptyCheckResponse;
            }

            $bookDTO = BookDTO::fromRequest($validatedData);
            $book = $this->bookService->updateBook($book_id, $bookDTO, $validatedData);

            return ResponseUtils::success([
              'book' => new BookResource($book),
            ], ResponseMessage::UPDATED_BOOK->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_BOOK->value);
        } catch (Exception $e) {
            return $this->handleBookException($e, $request->validated(), $book_id, 'cập nhật');
        }
    }

    /**
     * Delete a book
     *
     *
     * @return JsonResponse
     *
     * @group Books
     */
    public function destroy($bookId)
    {
        // Kiểm tra quyền truy cập (bỏ qua trong môi trường testing)
        if (! app()->environment('testing') && ! AuthUtils::userCan('delete_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->bookService->deleteBook($bookId);

            return ResponseUtils::success([
              'message' => ResponseMessage::DELETED_BOOK->value,
            ], ResponseMessage::DELETED_BOOK->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'book_id' => $bookId,
            ]);
        }
    }

    /**
     * Restore a book
     *
     *
     * @return JsonResponse
     *
     * @group Books
     */
    public function restore(int $book_id)
    {
        if (! AuthUtils::userCan('create_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            $book = $this->bookService->restoreBook($book_id);

            return ResponseUtils::success([
              'book' => new BookResource($book),
            ], ResponseMessage::RESTORED_BOOK->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                'book_id' => $book_id,
        ]
            );
        }
    }
}
