<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GenreStoreRequest;
use App\Http\Requests\V1\GenreUpdateRequest;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Http\Sorts\V1\GenreSort;
use App\Models\Genre;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    use HandlePagination;

    /**
     * Get all genres
     *
     * @return GenreCollection
     * @group Genres
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $genreSort = new GenreSort($request);
        $genres = $genreSort->apply(Genre::query())
                            ->paginate($this->getPerPage($request));

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
        $genreData = $request->validated()['data']['attributes'];
        $genre = Genre::create($genreData);

        return ResponseUtils::created([
            'genre' => new GenreResource($genre),
        ], ResponseMessage::CREATED_GENRE->value);
    }

    /**
     * Get a genre
     *
     * @param $genre_id
     *
     * @return JsonResponse
     * @group Genres
     * @unauthenticated
     */
    public function show($genre_id)
    {
        try {
            return ResponseUtils::success([
                'genre' => new GenreResource(Genre::findOrFail($genre_id)),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        }
    }

    /**
     * Update a genre
     *
     * @param GenreUpdateRequest $request
     * @param                    $genre_id
     *
     * @return JsonResponse
     * @group Genres
     */
    public function update(GenreUpdateRequest $request, $genre_id)
    {
        try {
            $genreData = $request->validated()['data']['attributes'];
            $genre = Genre::findOrFail($genre_id)->update($genreData);

            return ResponseUtils::success([
                'genre' => new GenreResource($genre),
            ], ResponseMessage::UPDATED_GENRE->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        }
    }

    /**
     * Delete a genre
     *
     * @param         $genre_id
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
            Genre::findOrFail($genre_id)->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_GENRE->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_GENRE->value);
        }
    }
}
