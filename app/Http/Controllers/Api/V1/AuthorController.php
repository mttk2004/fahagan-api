<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Author\AuthorDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthorStoreRequest;
use App\Http\Requests\V1\AuthorUpdateRequest;
use App\Http\Resources\V1\AuthorCollection;
use App\Http\Resources\V1\AuthorResource;
use App\Services\AuthorService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
  use HandlePagination;
  use HandleExceptions;
  use HandleValidation;

  protected AuthorService $authorService;
  protected string $entityName = 'author';

  public function __construct(AuthorService $authorService)
  {
    $this->authorService = $authorService;
  }

  /**
   * Get all authors
   *
   * @return AuthorCollection
   * @group Authors
   * @unauthenticated
   */
  public function index(Request $request)
  {
    $authors = $this->authorService->getAllAuthors($request, $this->getPerPage($request));

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
    if (! AuthUtils::userCan('create_authors')) {
      return ResponseUtils::forbidden();
    }

    try {
      $author = $this->authorService->createAuthor(
        AuthorDTO::fromRequest($request->validated())
      );

      return ResponseUtils::created([
        'author' => new AuthorResource($author),
      ], ResponseMessage::CREATED_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => $request->validated()
      ]);
    }
  }

  /**
   * Get an author
   *
   * @param $author_id
   *
   * @return JsonResponse
   * @group Authors
   * @unauthenticated
   */
  public function show($author_id)
  {
    try {
      $author = $this->authorService->getAuthorById($author_id);

      return ResponseUtils::success([
        'author' => new AuthorResource($author),
      ]);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'author_id' => $author_id,
      ]);
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
    if (! AuthUtils::userCan('edit_authors')) {
      return ResponseUtils::forbidden();
    }

    try {
      $validatedData = $request->validated();

      $emptyCheckResponse = $this->validateUpdateData($validatedData);
      if ($emptyCheckResponse) {
        return $emptyCheckResponse;
      }

      $author = $this->authorService->updateAuthor(
        $author_id,
        AuthorDTO::fromRequest($validatedData)
      );

      return ResponseUtils::success([
        'author' => new AuthorResource($author),
      ], ResponseMessage::UPDATED_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'request_data' => $request->validated()
      ]);
    }
  }

  /**
   * Delete an author
   *
   * @param         $author_id
   *
   * @return JsonResponse
   * @group Authors
   */
  public function destroy($author_id)
  {
    if (! AuthUtils::userCan('delete_authors')) {
      return ResponseUtils::forbidden();
    }

    try {
      $this->authorService->deleteAuthor($author_id);

      return ResponseUtils::noContent(ResponseMessage::DELETED_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleException($e, $this->entityName, [
        'author_id' => $author_id,
      ]);
    }
  }
}
