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
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
  use HandlePagination, HandleExceptions;

  protected PublisherService $publisherService;
  protected string $entityName = 'publisher';

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
    if (! AuthUtils::userCan('create_publishers')) {
      return ResponseUtils::forbidden();
    }

    try {
      $publisher = $this->publisherService->createPublisher(
        PublisherDTO::fromRequest($request->validated())
      );

      return ResponseUtils::created([
        'publisher' => new PublisherResource($publisher),
      ], ResponseMessage::CREATED_PUBLISHER->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => $request->validated()
      ]);
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
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'publisher_id' => $publisherId
      ]);
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
    if (! AuthUtils::userCan('edit_publishers')) {
      return ResponseUtils::forbidden();
    }

    try {
      $publisher = $this->publisherService->updatePublisher(
        $publisherId,
        PublisherDTO::fromRequest($request->validated())
      );

      return ResponseUtils::success([
        'publisher' => new PublisherResource($publisher),
      ], ResponseMessage::UPDATED_PUBLISHER->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'publisher_id' => $publisherId,
        'request_data' => $request->validated()
      ]);
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
    if (! AuthUtils::userCan('delete_publishers')) {
      return ResponseUtils::forbidden();
    }

    try {
      $this->publisherService->deletePublisher($publisherId);

      return ResponseUtils::noContent(ResponseMessage::DELETED_PUBLISHER->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'publisher_id' => $publisherId
      ]);
    }
  }
}
