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
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    use HandlePagination;

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
        $publishers = $publisherSort->apply(Publisher::query())
                                    ->paginate($this->getPerPage($request));

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

        return ResponseUtils::created([
            'publisher' => new PublisherResource($publisher),
        ], ResponseMessage::CREATED_PUBLISHER->value);
    }

    /**
     * Get a publisher
     *
     * @param $publisher_id
     *
     * @return JsonResponse
     * @group Publishers
     * @unauthenticated
     */
    public function show($publisher_id)
    {
        try {
            return ResponseUtils::success([
                'publisher' => new PublisherResource(Publisher::findOrFail($publisher_id)),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
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

            return ResponseUtils::success([
                'publisher' => new PublisherResource($publisher),
            ], ResponseMessage::UPDATED_PUBLISHER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
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
        if (! AuthUtils::userCan('delete_books')) {
            return ResponseUtils::forbidden();
        }

        try {
            // TODO: handle books that targets to the publisher before deleting it
            $publisher = Publisher::findOrFail($publisherId);
            $publisher->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_PUBLISHER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
        }
    }
}
