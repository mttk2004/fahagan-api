<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\Publisher\PublisherDTO;
use App\Filters\PublisherFilter;
use App\Http\Sorts\V1\PublisherSort;
use App\Models\Publisher;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublisherService
{
    /**
     * Lấy danh sách nhà xuất bản với filter và sort
     */
    public function getAllPublishers(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        $query = Publisher::query();

        // Apply filters
        $publisherFilter = new PublisherFilter($request);
        $query = $publisherFilter->apply($query);

        // Apply sorting
        $publisherSort = new PublisherSort($request);
        $query = $publisherSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo nhà xuất bản mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createPublisher(PublisherDTO $publisherDTO): Publisher
    {
        try {
            DB::beginTransaction();

            // Kiểm tra xem nhà xuất bản đã bị xóa mềm hay chưa
            $existingPublisher = Publisher::withTrashed()
              ->where('name', $publisherDTO->name)
              ->first();

            if ($existingPublisher && $existingPublisher->trashed()) {
                // Nếu đã bị xóa mềm, restore và cập nhật
                $existingPublisher->restore();
                $existingPublisher->update($publisherDTO->toArray());
                $publisher = $existingPublisher;
            } else {
                // Tạo nhà xuất bản mới
                $publisher = Publisher::create($publisherDTO->toArray());
            }

            DB::commit();

            return $publisher->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết nhà xuất bản
     *
     * @throws ModelNotFoundException
     */
    public function getPublisherById(string|int $publisherId): Publisher
    {
        return Publisher::with(['publishedBooks'])->findOrFail($publisherId);
    }

    /**
     * Cập nhật nhà xuất bản
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updatePublisher(string|int $publisherId, PublisherDTO $publisherDTO): Publisher
    {
        try {
            // Tìm nhà xuất bản hiện tại
            $publisher = Publisher::findOrFail($publisherId);

            DB::beginTransaction();

            // Cập nhật thông tin nhà xuất bản
            $publisher->update($publisherDTO->toArray());

            DB::commit();

            return $publisher->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Xóa nhà xuất bản
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deletePublisher(string|int $publisherId): void
    {
        try {
            $publisher = Publisher::findOrFail($publisherId);

            DB::beginTransaction();

            // Xóa nhà xuất bản
            $publisher->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
