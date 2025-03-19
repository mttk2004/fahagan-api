<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthorStoreRequest;
use App\Http\Requests\V1\AuthorUpdateRequest;
use App\Http\Resources\V1\AuthorCollection;
use App\Http\Resources\V1\AuthorResource;
use App\Http\Sorts\V1\AuthorSort;
use App\Models\Author;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
	use ApiResponses;

	/**
	 * Get all authors
	 *
	 * @return AuthorCollection
	 * @group Authors
	 * @unauthenticated
	 */
	public function index(Request $request)
	{
		$authorSort = new AuthorSort($request);
		$authors = $authorSort->apply(Author::query())->paginate();

		return new AuthorCollection($authors);
	}

	/**
	 * Create a new author
	 *
	 * @param AuthorStoreRequest $request
	 *
	 * @return AuthorResource
	 * @group Authors
	 */
	public function store(AuthorStoreRequest $request)
	{
		$validatedData = $request->validated();
		$authorData = $validatedData['data']['attributes'];

		$author = Author::create($authorData);

		return new AuthorResource($author);
	}

	/**
	 * Get an author
	 *
	 * @param Author $author
	 *
	 * @return void
	 * @group Authors
	 * @unauthenticated
	 */
	public function show(Author $author) {}

	/**
	 * Update an author
	 *
	 * @param Request $request
	 * @param Author  $author
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @group Authors
	 */
	public function update(AuthorUpdateRequest $request, $author_id) {
		try {
			$author = Author::findOrFail($author_id);
			$validatedData = $request->validated();
			$authorData = $validatedData['data']['attributes'];

			$author->update($authorData);

			return $this->ok('Cập nhật tác giả thành công.', [
				'author' => new AuthorResource($author),
			]);
		} catch (ModelNotFoundException) {
			return $this->error('Tác giả không tồn tại.', 404);
		}
	}

	/**
	 * Delete an author
	 *
	 * @param Author $author
	 *
	 * @return void
	 * @group Authors
	 */
	public function destroy(Author $author) {}
}
