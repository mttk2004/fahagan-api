<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookStoreRequest;
use App\Http\Requests\V1\BookUpdateRequest;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookResource;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class BookController extends Controller
{
	use ApiResponses;


	public function index(Request $request)
	{
		$bookSort = new BookSort($request);
		$books = $bookSort->apply(Book::query())->paginate();

		return new BookCollection($books);
	}

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

		return new BookResource($book);
	}

	public function show(Book $book)
	{
		return new BookResource($book);
	}

	public function update(BookUpdateRequest $request, $book_id)
	{
		try {
			$book = Book::findOrFail($book_id);
			$validatedData = $request->validated();
			$bookData = $validatedData['data']['attributes'];
			$bookData['publisher_id'] = $validatedData['data']['relationships']['publisher']['id'];

			$book->update($bookData);

			$authorIds = collect($validatedData['data']['relationships']['authors']['data'])
				->pluck('id')
				->toArray();
			$genreIds = collect($validatedData['data']['relationships']['genres']['data'])
				->pluck('id')
				->toArray();

			// Sync authors and genres
			$book->authors()->sync($authorIds);
			$book->genres()->sync($genreIds);

			return new BookResource($book);
		} catch (ModelNotFoundException) {
			return $this->error('Sách không tồn tại.', 404);
		}
	}

	public function destroy(Request $request, $bookId)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_books')) {
			return $this->error('Bạn không có quyền thực hiện hành động này.', 403);
		}

		try {
			$book = Book::findOrFail($bookId);
			$book->delete();

			return $this->ok('Xóa sách thành công.');
		} catch (ModelNotFoundException) {
			return $this->error('Sách không tồn tại.', 404);
		}
	}
}
