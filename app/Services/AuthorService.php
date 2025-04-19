<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\Author\AuthorDTO;
use App\DTOs\BaseDTO;
use App\Filters\AuthorFilter;
use App\Http\Sorts\V1\AuthorSort;
use App\Models\Author;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AuthorService extends BaseService
{
  /**
   * AuthorService constructor.
   */
  public function __construct()
  {
    $this->model = new Author();
    $this->filterClass = AuthorFilter::class;
    $this->sortClass = AuthorSort::class;
    $this->with = ['writtenBooks'];
  }

  /**
   * Lấy danh sách tác giả với filter và sort
   */
  public function getAllAuthors(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
  {
    return $this->getAll($request, $perPage);
  }

  /**
   * Tạo tác giả mới
   *
   * @throws ValidationException
   * @throws Exception
   */
  public function createAuthor(AuthorDTO $authorDTO): Author
  {
    return $this->create($authorDTO);
  }

  /**
   * Lấy thông tin chi tiết tác giả
   *
   * @throws ModelNotFoundException
   */
  public function getAuthorById(string|int $authorId): Author
  {
    return $this->getById($authorId);
  }

  /**
   * Cập nhật tác giả
   *
   * @throws ModelNotFoundException
   * @throws ValidationException
   * @throws Exception
   */
  public function updateAuthor(string|int $authorId, AuthorDTO $authorDTO): Author
  {
    return $this->update($authorId, $authorDTO);
  }

  /**
   * Xóa tác giả
   *
   * @throws ModelNotFoundException
   * @throws Exception
   */
  public function deleteAuthor(string|int $authorId): void
  {
    $this->delete($authorId);
  }

  /**
   * Find a trashed resource based on unique attributes
   *
   * @param BaseDTO $dto
   * @return Model|null
   */
  protected function findTrashed(BaseDTO $dto): ?Model
  {
    // Đảm bảo DTO là kiểu AuthorDTO trước khi tiếp tục
    if (! ($dto instanceof AuthorDTO) || ! isset($dto->name)) {
      return null;
    }

    return Author::withTrashed()
      ->where('name', $dto->name)
      ->onlyTrashed()
      ->first();
  }
}
