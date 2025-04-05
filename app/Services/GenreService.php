<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\Genre\GenreDTO;
use App\Filters\GenreFilter;
use App\Http\Sorts\V1\GenreSort;
use App\Models\Genre;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GenreService
{
    /**
     * Lấy danh sách thể loại với filter và sort
     */
    public function getAllGenres(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $query = Genre::query();

        // Apply filters
        $genreFilter = new GenreFilter($request);
        $query = $genreFilter->apply($query);

        // Apply sorting
        $genreSort = new GenreSort($request);
        $query = $genreSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo thể loại mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createGenre(GenreDTO $genreDTO): Genre
    {
        try {
            DB::beginTransaction();

            $genreData = $genreDTO->toArray();

            // Tự động tạo slug nếu không được cung cấp
            if (! isset($genreData['slug']) && isset($genreData['name'])) {
                $genreData['slug'] = Str::slug($genreData['name']);
            }

            // Tạo thể loại
            $genre = Genre::create($genreData);

            DB::commit();

            return $genre;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết thể loại
     *
     * @throws ModelNotFoundException
     */
    public function getGenreById(string|int $genreId): Genre
    {
        return Genre::findOrFail($genreId);
    }

    /**
     * Lấy thông tin chi tiết thể loại theo slug
     *
     * @throws ModelNotFoundException
     */
    public function getGenreBySlug(string $slug): Genre
    {
        return Genre::where('slug', $slug)->firstOrFail();
    }

    /**
     * Cập nhật thể loại
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateGenre(string|int $genreId, GenreDTO $genreDTO): Genre
    {
        try {
            // Tìm thể loại hiện tại
            $genre = Genre::findOrFail($genreId);

            DB::beginTransaction();

            $genreData = $genreDTO->toArray();

            // Tự động cập nhật slug nếu tên được cập nhật mà không cung cấp slug mới
            if (! isset($genreData['slug']) && isset($genreData['name']) && $genreData['name'] !== $genre->name) {
                $genreData['slug'] = Str::slug($genreData['name']);
            }

            // Cập nhật thông tin thể loại
            $genre->update($genreData);

            DB::commit();

            return $genre->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Xóa thể loại
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteGenre(string|int $genreId): void
    {
        try {
            $genre = Genre::findOrFail($genreId);

            DB::beginTransaction();

            // Xóa thể loại
            $genre->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Khôi phục thể loại đã xóa mềm
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function restoreGenre(string|int $genreId): Genre
    {
        try {
            $genre = Genre::withTrashed()->findOrFail($genreId);

            if (! $genre->trashed()) {
                throw new Exception('Thể loại này chưa bị xóa.');
            }

            DB::beginTransaction();

            // Khôi phục thể loại
            $genre->restore();

            DB::commit();

            return $genre->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
