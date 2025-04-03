<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Genre\GenreDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GenreStoreRequest;
use App\Http\Requests\V1\GenreUpdateRequest;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Services\GenreService;
use App\Traits\HandleGenreExceptions;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    use HandlePagination;
    use HandleGenreExceptions;

    public function __construct(
        private readonly GenreService $genreService
    ) {
    }

    /**
     * Get all genres
     *
     * @param Request $request
     *
     * @return GenreCollection|JsonResponse
     * @group Genres
     */
    public function index(Request $request)
    {
        if (! AuthUtils::userCan('view_genres')) {
            return ResponseUtils::forbidden();
        }

        $genres = $this->genreService->getAllGenres($request, $this->getPerPage($request));

        return new GenreCollection($genres);
    }

    /**
     * Create a new genre
     *
     * @param GenreStoreRequest $request
     *
     * @return JsonResponse
     * @group Genres
     */
    public function store(GenreStoreRequest $request)
    {
        try {
            $genreDTO = $this->createGenreDTOFromRequest($request);

            $genre = $this->genreService->createGenre($genreDTO);

            return ResponseUtils::created([
                'genre' => new GenreResource($genre),
            ], ResponseMessage::CREATED_GENRE->value);
        } catch (Exception $e) {
            return $this->handleGenreException($e, $request->validated(), null, 'tạo');
        }
    }

    /**
     * Get a genre
     *
     * @param $genre_id
     *
     * @return JsonResponse
     * @group Genres
     */
    public function show($genre_id)
    {
        try {
            if (! AuthUtils::userCan('view_genres')) {
                return ResponseUtils::forbidden();
            }

            $genre = $this->genreService->getGenreById($genre_id);

            return ResponseUtils::success([
                'genre' => new GenreResource($genre),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        }
    }

    /**
     * Get a genre by slug
     *
     * Phương thức này cho phép tìm kiếm thể loại sách theo slug thay vì ID.
     * Slug thường được sử dụng trong URL thân thiện (user-friendly URLs),
     * ví dụ: example.com/genres/science-fiction thay vì example.com/genres/123
     *
     * Được dùng trong các trường hợp:
     * 1. Routing trong trang web: Hiển thị trang chi tiết thể loại với URL thân thiện
     * 2. Truy vấn API: Khi client muốn tìm kiếm thể loại dựa trên tên thay vì ID
     * 3. SEO: URL có chứa slug dễ đọc giúp cải thiện SEO
     *
     * @param $slug
     *
     * @return JsonResponse
     * @group Genres
     */
    public function showBySlug($slug)
    {
        try {
            if (! AuthUtils::userCan('view_genres')) {
                return ResponseUtils::forbidden();
            }

            $genre = $this->genreService->getGenreBySlug($slug);

            return ResponseUtils::success([
                'genre' => new GenreResource($genre),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        }
    }

    /**
     * Update a genre
     *
     * @param GenreUpdateRequest $request
     * @param                   $genre_id
     *
     * @return JsonResponse
     * @group Genres
     */
    public function update(GenreUpdateRequest $request, $genre_id)
    {
        try {
            $genreDTO = $this->createGenreDTOFromRequest($request);

            if ($this->isEmptyUpdateData($request->validated())) {
                return ResponseUtils::badRequest('Không có dữ liệu nào để cập nhật.');
            }

            $genre = $this->genreService->updateGenre($genre_id, $genreDTO);

            return ResponseUtils::success([
                'genre' => new GenreResource($genre),
            ], ResponseMessage::UPDATED_GENRE->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        } catch (Exception $e) {
            return $this->handleGenreException($e, $request->validated(), $genre_id, 'cập nhật');
        }
    }

    /**
     * Delete a genre
     *
     * @param $genre_id
     *
     * @return JsonResponse
     * @group Genres
     */
    public function destroy($genre_id)
    {
        if (! AuthUtils::userCan('delete_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->genreService->deleteGenre($genre_id);

            return ResponseUtils::noContent(ResponseMessage::DELETED_GENRE->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        } catch (Exception $e) {
            return $this->handleGenreException($e, [], $genre_id, 'xóa');
        }
    }

    /**
     * Restore a soft deleted genre
     *
     * @param $genre_id
     *
     * @return JsonResponse
     * @group Genres
     */
    public function restore($genre_id)
    {
        if (! AuthUtils::userCan('restore_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $genre = $this->genreService->restoreGenre($genre_id);

            return ResponseUtils::success([
                'genre' => new GenreResource($genre),
            ], ResponseMessage::RESTORED_GENRE->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        } catch (Exception $e) {
            return $this->handleGenreException($e, [], $genre_id, 'khôi phục');
        }
    }

    /**
     * Tạo GenreDTO từ request đã validate
     *
     * @param GenreStoreRequest|GenreUpdateRequest $request
     * @return GenreDTO
     */
    private function createGenreDTOFromRequest(GenreStoreRequest|GenreUpdateRequest $request): GenreDTO
    {
        $validatedData = $request->validated();

        return new GenreDTO(
            name: $validatedData['name'] ?? null,
            slug: $validatedData['slug'] ?? null,
            description: $validatedData['description'] ?? null
        );
    }

    /**
     * Kiểm tra xem dữ liệu cập nhật có rỗng không
     *
     * @param array $validatedData
     * @return bool
     */
    private function isEmptyUpdateData(array $validatedData): bool
    {
        return empty($validatedData);
    }
}
