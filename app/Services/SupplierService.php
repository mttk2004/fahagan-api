<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\SupplierDTO;
use App\Http\Filters\V1\SupplierFilter;
use App\Http\Sorts\V1\SupplierSort;
use App\Models\Supplier;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SupplierService extends BaseService
{
    /**
     * SupplierService constructor.
     */
    public function __construct()
    {
        $this->model = new Supplier;
        $this->filterClass = SupplierFilter::class;
        $this->sortClass = SupplierSort::class;
        $this->with = ['suppliedBooks'];
    }

    /**
     * Lấy danh sách tất cả nhà cung cấp với filter và sắp xếp
     */
    public function getAllSuppliers(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Tạo nhà cung cấp mới
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function createSupplier(SupplierDTO $supplierDTO, ?array $bookIds = null): Supplier
    {
        $relations = [];
        // Sử dụng bookIds từ tham số hoặc từ DTO nếu tham số không có
        $bookIdsToUse = $bookIds ?? $supplierDTO->book_ids;

        if (! empty($bookIdsToUse)) {
            $relations['suppliedBooks'] = $bookIdsToUse;
        }

        return $this->create($supplierDTO, $relations);
    }

    /**
     * Lấy thông tin chi tiết nhà cung cấp
     *
     * @throws ModelNotFoundException
     */
    public function getSupplierById(string|int $supplierId): Supplier
    {
        return $this->getById($supplierId);
    }

    /**
     * Cập nhật thông tin nhà cung cấp
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateSupplier(string|int $supplierId, SupplierDTO $supplierDTO, ?array $bookIds = null): Supplier
    {
        // Sử dụng bookIds từ tham số hoặc từ DTO nếu tham số không có
        $bookIdsToUse = $bookIds ?? $supplierDTO->book_ids;

        $relations = null;
        if (! empty($bookIdsToUse)) {
            $relations = ['suppliedBooks' => $bookIdsToUse];
        }

        return $this->update($supplierId, $supplierDTO, $relations);
    }

    /**
     * Xóa nhà cung cấp
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteSupplier(string|int $supplierId): void
    {
        $this->delete($supplierId);
    }

    /**
     * Khôi phục nhà cung cấp đã xóa mềm
     *
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function restoreSupplier(string|int $supplierId): Supplier
    {
        return $this->restore($supplierId);
    }

    /**
     * Actions to perform before deleting a resource
     */
    protected function beforeDelete(\Illuminate\Database\Eloquent\Model $resource): void
    {
        // Đảm bảo tài nguyên là đối tượng Supplier trước khi tiếp tục
        if ($resource instanceof Supplier) {
            // Xóa mối quan hệ với sách
            $resource->suppliedBooks()->detach();
        }
    }

    /**
     * Get the message when trying to restore a resource that is not deleted
     */
    protected function getResourceNotDeletedMessage(): string
    {
        return 'Nhà cung cấp này chưa bị xóa.';
    }
}
