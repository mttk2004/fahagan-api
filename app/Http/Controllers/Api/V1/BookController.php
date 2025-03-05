<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;


class BookController extends Controller
{
	public function index()
	{
		return BookResource::collection(Book::all());
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
