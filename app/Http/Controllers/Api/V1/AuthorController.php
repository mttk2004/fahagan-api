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
use App\Traits\HandleAuthorExceptions;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
  use HandlePagination;
  use HandleAuthorExceptions;

  public function __construct(
    private readonly AuthorService $authorService
  ) {}

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
    try {
      if (! AuthUtils::userCan('create_authors')) {
        return ResponseUtils::forbidden();
      }

      $author = $this->authorService->createAuthor(
        AuthorDTO::fromRequest($request->validated())
      );

      return ResponseUtils::created([
        'author' => new AuthorResource($author),
      ], ResponseMessage::CREATED_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleAuthorException($e, $request->validated(), null, 'tạo');
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
    } catch (ModelNotFoundException) {
      return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
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
      if (! AuthUtils::userCan('edit_authors')) {
        return ResponseUtils::forbidden();
      }

      if ($this->isEmptyUpdateData($request->validated())) {
        return ResponseUtils::badRequest('Không có dữ liệu nào để cập nhật.');
      }

      $author = $this->authorService->updateAuthor(
        $author_id,
        AuthorDTO::fromRequest($request->validated())
      );

      return ResponseUtils::success([
        'author' => new AuthorResource($author),
      ], ResponseMessage::UPDATED_AUTHOR->value);
    } catch (ModelNotFoundException) {
      return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleAuthorException($e, $request->validated(), $author_id, 'cập nhật');
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
    } catch (ModelNotFoundException) {
      return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_AUTHOR->value);
    } catch (Exception $e) {
      return $this->handleAuthorException($e, [], $author_id, 'xóa');
    }
  }

  /**
   * Kiểm tra xem dữ liệu cập nhật có rỗng không
   *
   * @param array $validatedData
   * @return bool
   */
  private function isEmptyUpdateData(array $validatedData): bool
  {
    return empty($validatedData ?? []);
  }
}
