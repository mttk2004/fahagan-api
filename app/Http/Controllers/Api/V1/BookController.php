<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Book\BookDTO;
use App\Enums\ResponseMessage;
use App\Filters\BookFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookStoreRequest;
use App\Http\Requests\V1\BookUpdateRequest;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookResource;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use App\Services\BookService;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use HandlePagination;

    public function __construct(
        private readonly BookService $bookService
    ) {
    }

    /**
     * Get all books
     *
     * @param Request $request
     *
     * @return BookCollection
     * @group Books
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $books = $this->bookService->getAllBooks($request, $this->getPerPage($request));
        return new BookCollection($books);
    }

    /**
     * Create a new book
     *
     * @param BookStoreRequest $request
     *
     * @return JsonResponse
     * @group Books
     */
    public function store(BookStoreRequest $request)
    {
        $bookDTO = BookDTO::fromRequest($request->validated());
        $book = $this->bookService->createBook($bookDTO);

        return ResponseUtils::created([
            'book' => new BookResource($book),
        ], ResponseMessage::CREATED_BOOK->value);
    }

    /**
     * Get a book
     *
     * @param $book_id
     *
     * @return JsonResponse
     * @group Books
     * @unauthenticated
     */
    public function show($book_id)
    {
        try {
            $book = $this->bookService->getBookById($book_id);
            return ResponseUtils::success([
                'book' => new BookResource($book),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_BOOK->value);
        }
    }

    /**
     * Update a book
     *
     * @param BookUpdateRequest $request
     * @param                   $book_id
     *
     * @return JsonResponse
     * @group Books
     */
    public function update(BookUpdateRequest $request, $book_id)
    {
        try {
            $bookDTO = BookDTO::fromRequest($request->validated());
            $book = $this->bookService->updateBook($book_id, $bookDTO);

            return ResponseUtils::success([
                'book' => new BookResource($book),
            ], ResponseMessage::UPDATED_BOOK->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_BOOK->value);
        }
    }

    /**
     * Delete a book
     *
     * @param         $bookId
     *
     * @return JsonResponse
     * @group Books
     */
    public function destroy($bookId)
    {
        if (! AuthUtils::userCan('delete_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            $book = $this->bookService->deleteBook($bookId);

            return ResponseUtils::success([
                'book' => new BookResource($book),
            ], ResponseMessage::DELETED_BOOK->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_BOOK->value);
        }
    }
}
