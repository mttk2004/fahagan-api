<?php

namespace App\Services;

use App\DTOs\Author\AuthorDTO;
use App\Filters\AuthorFilter;
use App\Http\Sorts\V1\AuthorSort;
use App\Models\Author;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthorService
{
    /**
     * Lấy danh sách tác giả với filter và sort
     */
    public function getAllAuthors(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Author::query();

        // Apply filters
        $authorFilter = new AuthorFilter($request);
        $query = $authorFilter->apply($query);

        // Apply sorting
        $authorSort = new AuthorSort($request);
        $query = $authorSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo tác giả mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createAuthor(AuthorDTO $authorDTO): Author
    {
        try {
            DB::beginTransaction();

            // Tạo tác giả
            $author = Author::create($authorDTO->toArray());

            // Gán sách nếu có
            if (!empty($authorDTO->book_ids)) {
                $author->writtenBooks()->attach($authorDTO->book_ids);
            }

            DB::commit();

            return $author->fresh(['writtenBooks']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết tác giả
     *
     * @throws ModelNotFoundException
     */
    public function getAuthorById(string|int $authorId): Author
    {
        return Author::with(['writtenBooks'])->findOrFail($authorId);
    }

    /**
     * Cập nhật tác giả
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateAuthor(string|int $authorId, AuthorDTO $authorDTO): Author
    {
        try {
            // Tìm tác giả hiện tại
            $author = Author::findOrFail($authorId);

            DB::beginTransaction();

            // Cập nhật thông tin tác giả
            $author->update($authorDTO->toArray());

            // Cập nhật quan hệ với sách nếu có
            if (!empty($authorDTO->book_ids)) {
                $author->writtenBooks()->sync($authorDTO->book_ids);
            }

            DB::commit();

            return $author->fresh(['writtenBooks']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa tác giả
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteAuthor(string|int $authorId): void
    {
        try {
            $author = Author::findOrFail($authorId);

            DB::beginTransaction();

            // Xóa tác giả
            $author->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
