<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Resources\V1\GenreCollection;
use App\Http\Resources\V1\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;


class GenreController extends Controller
{
	/**
	 * Get all genres
	 *
	 * @return GenreCollection
	 * @group Genres
	 * @unauthenticated
	 */
	public function index()
	{
		return new GenreCollection(Genre::paginate());
	}

	/**
	 * Create a new genre
	 *
	 * @param Request $request
	 * @return void
	 * @group Genres
	 */
	public function store(Request $request) {}

	/**
	 * Get a genre
	 *
	 * @param Genre $genre
	 * @return GenreResource
	 * @group Genres
	 * @unauthenticated
	 */
	public function show(Genre $genre) {
		return new GenreResource($genre);
	}

	/**
	 * Update a genre
	 *
	 * @param Request $request
	 * @param Genre $genre
	 * @return void
	 * @group Genres
	 */
	public function update(Request $request, Genre $genre) {}

	/**
	 * Delete a genre
	 *
	 * @param Genre $genre
	 * @return void
	 * @group Genres
	 */
	public function destroy(Genre $genre) {}
}
