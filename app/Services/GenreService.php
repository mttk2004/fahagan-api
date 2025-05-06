<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\GenreDTO;
use App\Filters\GenreFilter;
use App\Http\Sorts\V1\GenreSort;
use App\Models\Genre;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GenreService extends BaseService
{
    /**
     * GenreService constructor.
     */
    public function __construct()
    {
        $this->model = new Genre;
        $this->filterClass = GenreFilter::class;
        $this->sortClass = GenreSort::class;
        $this->with = [];
    }

    /**
     * Lấy danh sách thể loại với filter và sort
     */
    public function getAllGenres(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Tạo thể loại mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createGenre(GenreDTO $genreDTO): Genre
    {
        $genreData = $genreDTO->toArray();

        // Tự động tạo slug nếu không được cung cấp
        if (! isset($genreData['slug']) && isset($genreData['name'])) {
            $genreData['slug'] = Str::slug($genreData['name']);

            // Cập nhật DTO để có slug mới
            $genreDTO = new GenreDTO(
                $genreData['name'],
                $genreData['slug'],
                $genreDTO->description
            );
        }

        return $this->create($genreDTO);
    }

    /**
     * Lấy thông tin chi tiết thể loại
     *
     * @throws ModelNotFoundException
     */
    public function getGenreById(string|int $genreId): Genre
    {
        return $this->getById($genreId);
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
        // Tìm thể loại hiện tại trước khi cập nhật
        $genre = $this->getById($genreId);

        $genreData = $genreDTO->toArray();

        // Tự động cập nhật slug nếu tên được cập nhật mà không cung cấp slug mới
        if (! isset($genreData['slug']) && isset($genreData['name']) && $genreData['name'] !== $genre->name) {
            $genreData['slug'] = Str::slug($genreData['name']);

            // Cập nhật DTO để có slug mới
            $genreDTO = new GenreDTO(
                $genreData['name'],
                $genreData['slug'],
                $genreDTO->description ?? $genre->description
            );
        }

        return $this->update($genreId, $genreDTO);
    }

    /**
     * Xóa thể loại
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteGenre(string|int $genreId): void
    {
        $this->delete($genreId);
    }

    /**
     * Khôi phục thể loại đã xóa mềm
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function restoreGenre(string|int $genreId): Genre
    {
        return $this->restore($genreId);
    }

    /**
     * Find a trashed resource based on unique attributes
     */
    protected function findTrashed(BaseDTO $dto): ?\Illuminate\Database\Eloquent\Model
    {
        // Đảm bảo DTO là kiểu GenreDTO trước khi tiếp tục
        if (! ($dto instanceof GenreDTO) || ! isset($dto->slug)) {
            return null;
        }

        return Genre::withTrashed()
            ->where('slug', $dto->slug)
            ->onlyTrashed()
            ->first();
    }

    /**
     * Get the message when trying to restore a resource that is not deleted
     */
    protected function getResourceNotDeletedMessage(): string
    {
        return 'Thể loại này chưa bị xóa.';
    }
}
