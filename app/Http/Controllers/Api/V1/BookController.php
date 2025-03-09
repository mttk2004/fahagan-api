<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookStoreRequest;
use App\Http\Resources\V1\BookCollection;
use App\Http\Resources\V1\BookResource;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use Illuminate\Http\Request;


class BookController extends Controller
{
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

	public function update($request, Book $book)
	{
		$book->update($request->validated());

		return new BookResource($book);
	}

	public function destroy(Book $book)
	{
		$book->delete();

		return response()->json();
	}
}
