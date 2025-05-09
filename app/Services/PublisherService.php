<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use App\DTOs\PublisherDTO;
use App\Http\Filters\V1\PublisherFilter;
use App\Http\Sorts\V1\PublisherSort;
use App\Models\Publisher;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Throwable;

class PublisherService extends BaseService
{
    /**
     * PublisherService constructor.
     */
    public function __construct()
    {
        $this->model = new Publisher;
        $this->filterClass = PublisherFilter::class;
        $this->sortClass = PublisherSort::class;
        $this->with = ['publishedBooks'];
    }

    /**
     * Lấy danh sách nhà xuất bản với filter và sort
     */
    public function getAllPublishers(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Tạo nhà xuất bản mới
     *
     * @throws ValidationException
     * @throws Exception|Throwable
     */
    public function createPublisher(PublisherDTO $publisherDTO): Publisher
    {
        // Kiểm tra xem tên nhà xuất bản đã tồn tại chưa (bao gồm cả đã xóa mềm)
        if (isset($publisherDTO->name)) {
            $existingPublisher = Publisher::where('name', $publisherDTO->name)->first();

            if ($existingPublisher) {
                throw ValidationException::withMessages([
                    'name' => ['Tên nhà xuất bản đã tồn tại. Vui lòng chọn tên khác.'],
                ]);
            }

            // Kiểm tra nhà xuất bản đã xóa mềm
            $trashedPublisher = Publisher::withTrashed()
                ->where('name', $publisherDTO->name)
                ->onlyTrashed()
                ->first();

            if ($trashedPublisher) {
                // Khôi phục nhà xuất bản đã xóa mềm và cập nhật thông tin
                $trashedPublisher->restore();
                $trashedPublisher->update($publisherDTO->toArray());

                return $trashedPublisher->fresh($this->with);
            }
        }

        return $this->create($publisherDTO);
    }

    /**
     * Lấy thông tin chi tiết nhà xuất bản
     *
     * @throws ModelNotFoundException
     */
    public function getPublisherById(string|int $publisherId): Model
    {
        return $this->getById($publisherId);
    }

    /**
     * Cập nhật nhà xuất bản
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     * @throws Throwable
     */
    public function updatePublisher(string|int $publisherId, PublisherDTO $publisherDTO): Model
    {
        return $this->update($publisherId, $publisherDTO);
    }

    /**
     * Xóa nhà xuất bản
     *
     * @throws ModelNotFoundException
     * @throws Exception
     * @throws Throwable
     */
    public function deletePublisher(string|int $publisherId): void
    {
        $this->delete($publisherId);
    }

    /**
     * Find a trashed resource based on unique attributes
     */
    protected function findTrashed(BaseDTO $dto): ?Model
    {
        // Đảm bảo DTO là kiểu PublisherDTO trước khi tiếp tục
        if (! ($dto instanceof PublisherDTO) || ! isset($dto->name)) {
            return null;
        }

        return Publisher::withTrashed()
            ->where('name', $dto->name)
            ->onlyTrashed()
            ->first();
    }
}
