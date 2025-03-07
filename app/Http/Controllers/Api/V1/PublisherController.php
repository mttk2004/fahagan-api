<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PublisherRequest;
use App\Http\Resources\V1\PublisherCollection;
use App\Http\Resources\V1\PublisherResource;
use App\Http\Sorts\V1\PublisherSort;
use App\Models\Publisher;
use Illuminate\Http\Request;


class PublisherController extends Controller
{
	public function index(Request $request)
	{
		$publisherSort = new PublisherSort($request);
		$publishers = $publisherSort->apply(Publisher::query())->paginate();

		return new PublisherCollection($publishers);
	}

	public function store(PublisherRequest $request)
	{
		return new PublisherResource(Publisher::create($request->validated()));
	}

	public function show(Publisher $publisher)
	{
		return new PublisherResource($publisher);
	}

	public function update(PublisherRequest $request, Publisher $publisher)
	{
		$publisher->update($request->validated());

		return new PublisherResource($publisher);
	}

	public function destroy(Publisher $publisher)
	{
		$publisher->delete();

		return response()->json();
	}
}
