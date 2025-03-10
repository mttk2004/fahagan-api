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
use Illuminate\Http\Request;


class PublisherController extends Controller
{
	use ApiResponses;

	public function index(Request $request)
	{
		$publisherSort = new PublisherSort($request);
		$publishers = $publisherSort->apply(Publisher::query())->paginate();

		return new PublisherCollection($publishers);
	}

	public function store(PublisherStoreRequest $request)
	{
		$publisherData = $request->validated();
		$publisher = Publisher::create($publisherData);

		return new PublisherResource($publisher);
	}

	public function show(Publisher $publisher)
	{
		return new PublisherResource($publisher);
	}

	public function update(PublisherUpdateRequest $request, $publisher_id)
	{
		try {
			$publisher = Publisher::findOrFail($publisher_id);
			$publisherData = $request->validated();
			$publisher->update($publisherData);

			return new PublisherResource($publisher);
		} catch (ModelNotFoundException) {
			return $this->error('Nhà xuất bản không tồn tại.', 404);
		}
	}

	public function destroy(Request $request, $publisherId)
	{
		$user = $request->user();
		if (!$user->hasPermissionTo('delete_books')) {
			return $this->error('Bạn không có quyền thực hiện hành động này.', 403);
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
