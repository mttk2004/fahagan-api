<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthorStoreRequest;
use App\Http\Requests\V1\AuthorUpdateRequest;
use App\Http\Resources\V1\AuthorCollection;
use App\Http\Resources\V1\AuthorResource;
use App\Http\Sorts\V1\AuthorSort;
use App\Models\Author;
use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
	 * @return JsonResponse
	 * @group Authors
	 */
	public function store(AuthorStoreRequest $request)
	{
		$validatedData = $request->validated();
		$authorData = $validatedData['data']['attributes'];

		$author = Author::create($authorData);

		return $this->ok(ResponseMessage::CREATED_AUTHOR->value, [
			'author' => new AuthorResource($author),
		]);
	}

	/**
	 * Get an author
	 *
	 * @param $author_id
	 *
	 * @return AuthorResource|JsonResponse
	 * @group Authors
	 * @unauthenticated
	 */
	public function show($author_id)
	{
		try {
			return new AuthorResource(Author::findOrFail($author_id));
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
		}
	}

	/**
	 * Update an author
	 *
	 * @param AuthorUpdateRequest $request
	 * @param                     $author_id
	 *
	 * @return JsonResponse
	 * @group Authors
	 */
	public function update(AuthorUpdateRequest $request, $author_id)
	{
		try {
			$author = Author::findOrFail($author_id);
			$validatedData = $request->validated();
			$authorData = $validatedData['data']['attributes'];

			$author->update($authorData);

			return $this->ok(ResponseMessage::UPDATED_AUTHOR->value, [
				'author' => new AuthorResource($author),
			]);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
		}
	}

	/**
	 * Delete an author
	 *
	 * @param Request $request
	 * @param         $author_id
	 *
	 * @return JsonResponse
	 * @group Authors
	 */
	public function destroy(Request $request, $author_id)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_authors')) {
			return $this->forbidden();
		}

		try {
			Author::findOrFail($author_id)->delete();

			return $this->ok(ResponseMessage::DELETED_AUTHOR->value);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
		}
	}
}
