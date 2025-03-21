<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Http\Sorts\V1\GenreSort;
use App\Models\Genre;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class GenreController extends Controller
{
	use ApiResponses;


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
		$genres = $genreSort->apply(Genre::query())->paginate();

		return new GenreCollection($genres);
	}

	/**
	 * Create a new genre
	 *
	 * @param Request $request
	 *
	 * @return void
	 * @group Genres
	 */
	public function store(Request $request) {}

	/**
	 * Get a genre
	 *
	 * @param $genre_id
	 *
	 * @return GenreResource|JsonResponse
	 * @group Genres
	 * @unauthenticated
	 */
	public function show($genre_id)
	{
		try {
			return new GenreResource(Genre::findOrFail($genre_id));
		} catch (ModelNotFoundException) {
			return $this->notFound('Thể loại không tồn tại');
		}
	}

	/**
	 * Update a genre
	 *
	 * @param Request $request
	 * @param Genre   $genre
	 *
	 * @return void
	 * @group Genres
	 */
	public function update(Request $request, Genre $genre) {}

	/**
	 * Delete a genre
	 *
	 * @param Genre $genre
	 *
	 * @return void
	 * @group Genres
	 */
	public function destroy(Genre $genre) {}
}
