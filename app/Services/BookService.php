<?php

namespace App\Services;

use App\DTOs\Book\BookDTO;
use App\Filters\BookFilter;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BookService
{
    /**
     * Lấy danh sách sách với filter và sort
     */
    public function getAllBooks(Request $request, int $perPage = 15): LengthAwarePaginator
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
     * Tạo sách mới
     */
    public function createBook(BookDTO $bookDTO): Book
    {
        // Tạo sách
        $book = Book::create($bookDTO->toArray());

        // Gán tác giả
        if (!empty($bookDTO->author_ids)) {
            $book->authors()->attach($bookDTO->author_ids);
        }

        // Gán thể loại
        if (!empty($bookDTO->genre_ids)) {
            $book->genres()->attach($bookDTO->genre_ids);
        }

        return $book->fresh(['authors', 'genres', 'publisher']);
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
     */
    public function updateBook(string|int $bookId, BookDTO $bookDTO): Book
    {
        $book = Book::findOrFail($bookId);

        // Cập nhật thông tin cơ bản
        $book->update($bookDTO->toArray());

        // Đồng bộ tác giả nếu có
        if (!empty($bookDTO->author_ids)) {
            $book->authors()->sync($bookDTO->author_ids);
        }

        // Đồng bộ thể loại nếu có
        if (!empty($bookDTO->genre_ids)) {
            $book->genres()->sync($bookDTO->genre_ids);
        }

        return $book->fresh(['authors', 'genres', 'publisher']);
    }

    /**
     * Xóa sách
     *
     * @throws ModelNotFoundException
     */
    public function deleteBook(string|int $bookId): Book
    {
        $book = Book::findOrFail($bookId);

        // Xóa các mối quan hệ discount liên quan đến sách này
        $book->getAllActiveDiscounts()->each(function ($discount) use ($book) {
            $discount->targets()->where('target_id', $book->id)->delete();
        });

        // Soft delete sách
        $book->delete();

        return $book;
    }
}
