<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookRequest;
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

	public function store(BookRequest $request)
	{
		return new BookResource(Book::create($request->validated()));
	}

	public function show(Book $book)
	{
		return new BookResource($book);
	}

	public function update(BookRequest $request, Book $book)
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
