<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\GenreDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GenreStoreRequest;
use App\Http\Requests\V1\GenreUpdateRequest;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Services\GenreService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    use HandleExceptions;
    use HandlePagination;
    use HandleValidation;

    public function __construct(
        private readonly GenreService $genreService,
        private readonly string $entityName = 'genre'
    ) {
    }

    /**
     * Get all genres
     *
     * @return GenreCollection
     * @group Genres
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $genres = $this->genreService->getAllGenres($request, $this->getPerPage($request));

        return new GenreCollection($genres);
    }

    /**
     * Create a new genre
     *
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     */
    public function store(GenreStoreRequest $request)
    {
        if (! AuthUtils::userCan('create_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $genre = $this->genreService->createGenre(
                GenreDTO::fromRequest($request->validated())
            );

            return ResponseUtils::created([
              'genre' => new GenreResource($genre),
            ], ResponseMessage::CREATED_GENRE->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'request_data' => $request->validated(),
            ]);
        }
    }

    /**
     * Get a genre
     *
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     */
    public function show($genre_id)
    {
        try {
            $genre = $this->genreService->getGenreById($genre_id);

            return ResponseUtils::success([
              'genre' => new GenreResource($genre),
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'genre_id' => $genre_id,
            ]);
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
     * @param string $slug
     * @throws Exception
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     */
    public function showBySlug($slug)
    {
        try {
            $genre = $this->genreService->getGenreBySlug($slug);

            return ResponseUtils::success([
              'genre' => new GenreResource($genre),
            ]);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'slug' => $slug,
            ]);
        }
    }

    /**
     * Update a genre
     *
     * @param GenreUpdateRequest $request
     * @param int $genre_id
     * @throws Exception
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     */
    public function update(GenreUpdateRequest $request, $genre_id)
    {
        if (! AuthUtils::userCan('edit_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $validatedData = $request->validated();

            $emptyCheckResponse = $this->validateUpdateData($validatedData);
            if ($emptyCheckResponse) {
                return $emptyCheckResponse;
            }

            $genre = $this->genreService->updateGenre(
                $genre_id,
                GenreDTO::fromRequest($validatedData)
            );

            return ResponseUtils::success([
              'genre' => new GenreResource($genre),
            ], ResponseMessage::UPDATED_GENRE->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'request_data' => $request->validated(),
            ]);
        }
    }

    /**
     * Delete a genre
     *
     * @param int $genre_id
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     * @throws Exception
     */
    public function destroy($genre_id)
    {
        if (! AuthUtils::userCan('delete_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->genreService->deleteGenre($genre_id);

            return ResponseUtils::noContent(ResponseMessage::DELETED_GENRE->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'genre_id' => $genre_id,
            ]);
        }
    }

    /**
     * Restore a soft deleted genre
     *
     * @param int $genre_id
     * @return JsonResponse
     * @group Genres
     */
    public function restore($genre_id)
    {
        if (! AuthUtils::userCan('create_genres')) {
            return ResponseUtils::forbidden();
        }

        try {
            $genre = $this->genreService->restoreGenre($genre_id);

            return ResponseUtils::success([
              'genre' => new GenreResource($genre),
            ], ResponseMessage::RESTORED_GENRE->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'genre_id' => $genre_id,
            ]);
        }
    }
}
