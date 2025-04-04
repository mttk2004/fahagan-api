<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Supplier\SupplierDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupplierStoreRequest;
use App\Http\Requests\V1\SupplierUpdateRequest;
use App\Http\Resources\V1\SupplierCollection;
use App\Http\Resources\V1\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Traits\HandlePagination;
use App\Traits\HandleSupplierExceptions;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use HandlePagination;
    use HandleSupplierExceptions;

    public function __construct(
        private readonly SupplierService $supplierService
    ) {
    }

    /**
     * Get all suppliers
     *
     * @param Request $request
     *
     * @return JsonResponse|SupplierCollection
     * @group Supplier
     */
    public function index(Request $request)
    {
        if (! AuthUtils::userCan('view_suppliers')) {
            return ResponseUtils::forbidden();
        }

        $suppliers = $this->supplierService->getAllSuppliers($request, $this->getPerPage($request));

        return new SupplierCollection($suppliers);
    }

    /**
     * Create a new supplier
     *
     * @param SupplierStoreRequest $request
     *
     * @return JsonResponse
     * @group Supplier
     */
    public function store(SupplierStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $supplierDTO = SupplierDTO::fromRequestData($validatedData);

            // Lấy book IDs nếu có
            $bookIds = $validatedData['data']['relationships']['books']['data'] ?? [];
            $bookIds = collect($bookIds)->pluck('id')->toArray();

            $supplier = $this->supplierService->createSupplier($supplierDTO, $bookIds);

            return ResponseUtils::created([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::CREATED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleSupplierException($e, $request->validated(), null, 'tạo');
        }
    }

    /**
     * Get a supplier
     *
     * @param $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     */
    public function show($supplier_id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById($supplier_id);

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ]);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_SUPPLIER->value);
        }
    }

    /**
     * Update a supplier
     *
     * @param SupplierUpdateRequest $request
     * @param                       $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     */
    public function update(SupplierUpdateRequest $request, $supplier_id)
    {
        try {
            $validatedData = $request->validated();
            $supplierDTO = SupplierDTO::fromRequestData($validatedData);

            // Lấy book IDs nếu có
            $bookIds = null;
            if (isset($validatedData['books'])) {
                $bookIds = $validatedData['books'];
            }

            $supplier = $this->supplierService->updateSupplier($supplier_id, $supplierDTO, $bookIds);

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::UPDATED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleSupplierException($e, $request->validated(), $supplier_id, 'cập nhật');
        }
    }

    /**
     * Delete a supplier
     *
     * @param $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     */
    public function destroy($supplier_id)
    {
        if (! AuthUtils::userCan('delete_suppliers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $this->supplierService->deleteSupplier($supplier_id);

            return ResponseUtils::noContent(ResponseMessage::DELETED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleSupplierException($e, [], $supplier_id, 'xóa');
        }
    }

    /**
     * Restore a soft deleted supplier
     *
     * @param $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     */
    public function restore($supplier_id)
    {
        if (! AuthUtils::userCan('restore_suppliers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $supplier = $this->supplierService->restoreSupplier($supplier_id);

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::RESTORED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleSupplierException($e, [], $supplier_id, 'khôi phục');
        }
    }
}
