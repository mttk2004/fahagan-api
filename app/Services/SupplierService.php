<?php

namespace App\Services;

use App\DTOs\Supplier\SupplierDTO;
use App\Filters\SupplierFilter;
use App\Http\Sorts\V1\SupplierSort;
use App\Models\Supplier;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    /**
     * Lấy danh sách tất cả nhà cung cấp với filter và sắp xếp
     *
     * @param Request $request
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllSuppliers(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Supplier::query();

        // Apply filters
        $supplierFilter = new SupplierFilter($request);
        $query = $supplierFilter->apply($query);

        // Apply sorting
        $supplierSort = new SupplierSort($request);
        $query = $supplierSort->apply($query);

        // Paginate
        return $query->paginate($perPage);
    }

    /**
     * Tạo nhà cung cấp mới
     *
     * @param SupplierDTO $supplierDTO
     * @param array|null $bookIds
     * @return Supplier
     * @throws ValidationException
     * @throws Exception
     */
    public function createSupplier(SupplierDTO $supplierDTO, ?array $bookIds = null): Supplier
    {
        try {
            DB::beginTransaction();

            // Tạo nhà cung cấp
            $supplier = Supplier::create($supplierDTO->toArray());

            // Liên kết với books nếu có
            if ($bookIds && !empty($bookIds)) {
                $supplier->suppliedBooks()->attach($bookIds);
            }

            DB::commit();

            return $supplier->fresh()->load('suppliedBooks');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy thông tin chi tiết nhà cung cấp
     *
     * @param string|int $supplierId
     * @return Supplier
     * @throws ModelNotFoundException
     */
    public function getSupplierById(string|int $supplierId): Supplier
    {
        return Supplier::with('suppliedBooks')->findOrFail($supplierId);
    }

    /**
     * Cập nhật thông tin nhà cung cấp
     *
     * @param string|int $supplierId
     * @param SupplierDTO $supplierDTO
     * @param array|null $bookIds
     * @return Supplier
     * @throws ModelNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateSupplier(string|int $supplierId, SupplierDTO $supplierDTO, ?array $bookIds = null): Supplier
    {
        try {
            // Tìm nhà cung cấp hiện tại
            $supplier = Supplier::findOrFail($supplierId);

            DB::beginTransaction();

            // Cập nhật thông tin nhà cung cấp
            $supplier->update($supplierDTO->toArray());

            // Cập nhật sách liên quan nếu có
            if ($bookIds !== null) {
                $supplier->suppliedBooks()->sync($bookIds);
            }

            DB::commit();

            return $supplier->fresh()->load('suppliedBooks');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa nhà cung cấp
     *
     * @param string|int $supplierId
     * @return void
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteSupplier(string|int $supplierId): void
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);

            DB::beginTransaction();

            // Xóa mối quan hệ với sách
            $supplier->suppliedBooks()->detach();

            // Xóa nhà cung cấp
            $supplier->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Khôi phục nhà cung cấp đã xóa mềm
     *
     * @param string|int $supplierId
     * @return Supplier
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function restoreSupplier(string|int $supplierId): Supplier
    {
        try {
            $supplier = Supplier::withTrashed()->findOrFail($supplierId);

            if (!$supplier->trashed()) {
                throw new Exception('Nhà cung cấp này chưa bị xóa.');
            }

            DB::beginTransaction();

            // Khôi phục nhà cung cấp
            $supplier->restore();

            DB::commit();

            return $supplier->fresh()->load('suppliedBooks');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
