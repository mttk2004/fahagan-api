<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Publisher\PublisherDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PublisherStoreRequest;
use App\Http\Requests\V1\PublisherUpdateRequest;
use App\Http\Resources\V1\PublisherCollection;
use App\Http\Resources\V1\PublisherResource;
use App\Services\PublisherService;
use App\Traits\HandlePagination;
use App\Utils\ResponseUtils;
use App\Utils\AuthUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublisherController extends Controller
{
    use HandlePagination;

    protected PublisherService $publisherService;

    public function __construct(PublisherService $publisherService)
    {
        $this->publisherService = $publisherService;
    }

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
        $publishers = $this->publisherService->getAllPublishers(
            $request,
            $this->getPerPage($request)
        );

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
        try {
            $publisherDTO = PublisherDTO::fromRequest($request->validated());
            $publisher = $this->publisherService->createPublisher($publisherDTO);

            return ResponseUtils::created([
                'publisher' => new PublisherResource($publisher),
            ], ResponseMessage::CREATED_PUBLISHER->value);
        } catch (ValidationException $e) {
            return ResponseUtils::validationError('Dữ liệu không hợp lệ.', $e->errors());
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Get a publisher
     *
     * @param int $publisherId
     *
     * @return JsonResponse
     * @group Publishers
     * @unauthenticated
     */
    public function show(int $publisherId)
    {
        try {
            $publisher = $this->publisherService->getPublisherById($publisherId);

            return ResponseUtils::success([
                'publisher' => new PublisherResource($publisher),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Update a publisher
     *
     * @param PublisherUpdateRequest $request
     * @param int $publisherId
     *
     * @return JsonResponse
     * @group Publishers
     */
    public function update(PublisherUpdateRequest $request, int $publisherId)
    {
        try {
            $publisherDTO = PublisherDTO::fromRequest($request->validated());
            $publisher = $this->publisherService->updatePublisher($publisherId, $publisherDTO);

            return ResponseUtils::success([
                'publisher' => new PublisherResource($publisher),
            ], ResponseMessage::UPDATED_PUBLISHER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
        } catch (ValidationException $e) {
            return ResponseUtils::validationError('Dữ liệu không hợp lệ.', $e->errors());
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }

    /**
     * Delete a publisher
     *
     * @param int $publisherId
     *
     * @return JsonResponse
     * @group Publishers
     */
    public function destroy(int $publisherId)
    {
        // Kiểm tra quyền xóa
        if (!AuthUtils::userCan('delete_publishers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->publisherService->deletePublisher($publisherId);

            return ResponseUtils::noContent(ResponseMessage::DELETED_PUBLISHER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
        } catch (Exception $e) {
            return ResponseUtils::serverError($e->getMessage());
        }
    }
}
