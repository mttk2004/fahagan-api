<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PublisherStoreRequest;
use App\Http\Requests\V1\PublisherUpdateRequest;
use App\Http\Resources\V1\PublisherCollection;
use App\Http\Resources\V1\PublisherResource;
use App\Http\Sorts\V1\PublisherSort;
use App\Models\Publisher;
use App\Traits\ApiResponses;
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
	 * @return PublisherResource
	 * @group Publishers
	 */
	public function store(PublisherStoreRequest $request)
	{
		$publisherData = $request->validated();
		$publisher = Publisher::create($publisherData);

		return new PublisherResource($publisher);
	}

	/**
	 * Get a publisher
	 *
	 * @param Publisher $publisher
	 * @return PublisherResource
	 * @group Publishers
	 * @unauthenticated
	 */
	public function show(Publisher $publisher)
	{
		return new PublisherResource($publisher);
	}

	/**
	 * Update a publisher
	 *
	 * @param PublisherUpdateRequest $request
	 * @param $publisher_id
	 * @return JsonResponse|PublisherResource
	 * @group Publishers
	 */
	public function update(PublisherUpdateRequest $request, $publisher_id)
	{
		try {
			$publisherData = $request->validated();
			$publisher = Publisher::findOrFail($publisher_id)->update($publisherData);

			return new PublisherResource($publisher);
		} catch (ModelNotFoundException) {
			return $this->error('Nhà xuất bản không tồn tại.', 404);
		}
	}

	/**
	 * Delete a publisher
	 *
	 * @param Request $request
	 * @param $publisherId
	 * @return JsonResponse
	 * @group Publishers
	 */
	public function destroy(Request $request, $publisherId)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_books')) {
			return $this->forbidden();
		}

		try {
			$publisher = Publisher::findOrFail($publisherId);
			$publisher->delete();

			return $this->ok('Xóa nhà xuất bản thành công.');
		} catch (ModelNotFoundException) {
			return $this->error('Nhà xuất bản không tồn tại.', 404);
		}
	}
}
