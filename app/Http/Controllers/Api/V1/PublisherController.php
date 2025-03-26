<?php

namespace App\Http\Controllers\Api\V1;


use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PublisherStoreRequest;
use App\Http\Requests\V1\PublisherUpdateRequest;
use App\Http\Resources\V1\PublisherCollection;
use App\Http\Resources\V1\PublisherResource;
use App\Http\Sorts\V1\PublisherSort;
use App\Models\Publisher;
use App\Traits\ApiResponses;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PublisherController extends Controller
{
	use ApiResponses;


	/**
	 * Get all publishers
	 *
	 * @param Request $request
	 *
	 * @return PublisherCollection
	 * @group Publishers
	 * @unauthenticated
	 */
	public function index(Request $request)
	{
		$publisherSort = new PublisherSort($request);
		$publishers = $publisherSort->apply(Publisher::query())->paginate();

		return new PublisherCollection($publishers);
	}

	/**
	 * Create a new publisher
	 *
	 * @param PublisherStoreRequest $request
	 *
	 * @return JsonResponse
	 * @group Publishers
	 */
	public function store(PublisherStoreRequest $request)
	{
		$publisherData = $request->validated()['data']['attributes'];
		$publisher = Publisher::create($publisherData);

		return $this->ok(ResponseMessage::CREATED_PUBLISHER->value, [
			'publisher' => new PublisherResource($publisher),
		]);
	}

	/**
	 * Get a publisher
	 *
	 * @param $publisher_id
	 *
	 * @return PublisherResource|JsonResponse
	 * @group Publishers
	 * @unauthenticated
	 */
	public function show($publisher_id)
	{
		try {
			return new PublisherResource(Publisher::findOrFail($publisher_id));
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
		}
	}

	/**
	 * Update a publisher
	 *
	 * @param PublisherUpdateRequest $request
	 * @param                        $publisher_id
	 *
	 * @return JsonResponse
	 * @group Publishers
	 */
	public function update(PublisherUpdateRequest $request, $publisher_id)
	{
		try {
			$publisherData = $request->validated()['data']['attributes'];
			$publisher = Publisher::findOrFail($publisher_id)->update($publisherData);

			return $this->ok(ResponseMessage::UPDATED_PUBLISHER->value, [
				'publisher' => new PublisherResource($publisher),
			]);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
		}
	}

	/**
	 * Delete a publisher
	 *
	 * @param         $publisherId
	 *
	 * @return JsonResponse
	 * @group Publishers
	 */
	public function destroy($publisherId)
	{
		$user = Auth::guard('sanctum')->user();
		if (!$user->hasPermissionTo('delete_books')) {
			return $this->unauthorized();
		}

		try {
			// TODO: handle books that targets to the publisher before deleting it
			$publisher = Publisher::findOrFail($publisherId);
			$publisher->delete();

			return $this->ok(ResponseMessage::DELETED_PUBLISHER->value);
		} catch (ModelNotFoundException) {
			return $this->notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
		}
	}
}
