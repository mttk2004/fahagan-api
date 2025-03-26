<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookStoreRequest;
use App\Http\Requests\V1\BookUpdateRequest;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookResource;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class BookController extends Controller
{
	/**
	 * Get all books
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @group Books
	 * @unauthenticated
	 */
	public function index(Request $request)
	{
		$bookSort = new BookSort($request);
		$books = $bookSort->apply(Book::query())->paginate();

		return ResponseUtils::success([
			'books' => new BookCollection($books),
		]);
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
		$validatedData = $request->validated();
		$bookData = $validatedData['data']['attributes'];

		// Ensure sold_count and available_count are set to 0 by default
		$bookData['sold_count'] = 0;
		$bookData['available_count'] = 0;

		// Set publisher_id
		$bookData['publisher_id'] = $validatedData['data']['relationships']['publisher']['id'];

		// Create book
		$book = Book::create($bookData);

		// Attach authors
		$book->authors()->attach(
			collect($validatedData['data']['relationships']['authors']['data'])
				->pluck('id')
				->toArray()
		);

		// Attach genres
		$book->genres()->attach(
			collect($validatedData['data']['relationships']['genres']['data'])
				->pluck('id')
				->toArray()
		);

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
			return ResponseUtils::success([
				'book' => new BookResource(Book::findOrFail($book_id)),
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
			$book = Book::findOrFail($book_id);
			$validatedData = $request->validated();
			$bookData = $validatedData['data']['attributes'];

			if (isset($validatedData['data']['relationships']['publisher']['id'])) {
				$bookData['publisher_id']
					= $validatedData['data']['relationships']['publisher']['id'];
			}

			$book->update($bookData);

			if (isset($validatedData['data']['relationships']['authors']['data'])) {
				$authorIds = collect($validatedData['data']['relationships']['authors']['data'])
					->pluck('id')
					->toArray();
				$book->authors()->sync($authorIds);
			}

			if (isset($validatedData['data']['relationships']['genres']['data'])) {
				$genreIds = collect($validatedData['data']['relationships']['genres']['data'])
					->pluck('id')
					->toArray();
				$book->genres()->sync($genreIds);
			}

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
		if (!AuthUtils::userCan('delete_books')) {
			return ResponseUtils::forbidden();
		}

		try {
			$book = Book::findOrFail($bookId);

			// Delete all discount targets (discount_targets pivot table) that target this book
			$book->getAllActiveDiscounts()->each(function($discount) use ($book) {
				$discount->targets()->where('target_id', $book->id)->delete();
			});

			$book->delete();

			return ResponseUtils::success([
				'book' => new BookResource($book),
			], ResponseMessage::DELETED_BOOK->value);
		} catch (ModelNotFoundException) {
			return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_BOOK->value);
		}
	}
}
