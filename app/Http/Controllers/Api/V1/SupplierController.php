<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Supplier\SupplierDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupplierStoreRequest;
use App\Http\Requests\V1\SupplierUpdateRequest;
use App\Http\Resources\V1\SupplierCollection;
use App\Http\Resources\V1\SupplierResource;
use App\Services\SupplierService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Traits\JsonApi\HandleJsonApi;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use HandlePagination;
    use HandleExceptions;
    use HandleValidation;
    use HandleJsonApi;

    public function __construct(
        private readonly SupplierService $supplierService,
        private readonly string $entityName = 'supplier'
    ) {}

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
        if (! AuthUtils::userCan('create_suppliers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $validatedData = $request->validated();

            // Validate JSON:API format
            $formatError = $this->validateJsonApiFormat($validatedData);
            if ($formatError) {
                return $formatError;
            }

            // Extract book IDs from relationships
            $bookIds = $this->extractRelationshipIds($validatedData, 'books');

            $supplier = $this->supplierService->createSupplier(
                SupplierDTO::fromRequest($validatedData),
                $bookIds
            );

            return ResponseUtils::created([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::CREATED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'request_data' => $request->validated(),
                ]
            );
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
        if (! AuthUtils::userCan('view_suppliers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $supplier = $this->supplierService->getSupplierById($supplier_id);

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ]);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'supplier_id' => $supplier_id,
                ]
            );
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
     * @unauthenticated
     */
    public function update(SupplierUpdateRequest $request, $supplier_id)
    {
        if (! AuthUtils::userCan('edit_suppliers')) {
            return ResponseUtils::forbidden();
        }

        try {
            $validatedData = $request->validated();

            // Validate JSON:API format
            $formatError = $this->validateJsonApiFormat($validatedData);
            if ($formatError) {
                return $formatError;
            }

            $emptyCheckResponse = $this->validateUpdateData($validatedData);
            if ($emptyCheckResponse) {
                return $emptyCheckResponse;
            }

            // Extract book IDs from relationships - null if relationship not included
            $bookIds = $this->extractRelationshipIds($validatedData, 'books', false);

            $supplier = $this->supplierService->updateSupplier(
                $supplier_id,
                SupplierDTO::fromRequest($validatedData),
                $bookIds
            );

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::UPDATED_SUPPLIER->value);
        } catch (Exception $e) {
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'supplier_id' => $supplier_id,
                    'request_data' => $request->validated(),
                ]
            );
        }
    }

    /**
     * Delete a supplier
     *
     * @param $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     * @authenticated
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
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'supplier_id' => $supplier_id,
                ]
            );
        }
    }

    /**
     * Restore a soft deleted supplier
     *
     * @param $supplier_id
     *
     * @return JsonResponse
     * @group Supplier
     * @authenticated
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
            return $this->handleException(
                $e,
                $this->entityName,
                [
                    'supplier_id' => $supplier_id,
                ]
            );
        }
    }
}
