<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseMessage;
use App\Filters\SupplierFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupplierStoreRequest;
use App\Http\Requests\V1\SupplierUpdateRequest;
use App\Http\Resources\V1\SupplierCollection;
use App\Http\Resources\V1\SupplierResource;
use App\Http\Sorts\V1\SupplierSort;
use App\Models\Supplier;
use App\Traits\HandlePagination;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use HandlePagination;

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

        $query = Supplier::query();

        // Apply filters
        $supplierFilter = new SupplierFilter($request);
        $query = $supplierFilter->apply($query);

        // Apply sorting
        $supplierSort = new SupplierSort($request);
        $query = $supplierSort->apply($query);

        // Get paginated results
        $suppliers = $query->paginate($this->getPerPage($request));

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
        $validatedData = $request->validated()['data'];
        $supplier = Supplier::create($validatedData['attributes']);

        // Attach books to the supplier
        $supplier->suppliedBooks()->attach(
            collect($validatedData['relationships']['books']['data'])
                ->pluck('id')
                ->toArray()
        );

        return ResponseUtils::created([
            'supplier' => new SupplierResource($supplier),
        ], ResponseMessage::CREATED_SUPPLIER->value);
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
            $supplier = Supplier::findOrFail($supplier_id);

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
            $supplier = Supplier::findOrFail($supplier_id);
            $validatedData = $request->validated()['data'];

            // Sync books with the supplier
            $books = $validatedData['relationships']['books']['data'] ?? null;
            if ($books) {
                $supplier->suppliedBooks()->sync(collect($books)->pluck('id')->toArray());
            }

            // Update supplier attributes
            $attributes = $validatedData['attributes'] ?? null;
            if ($attributes) {
                $supplier->update($attributes);
            }

            return ResponseUtils::success([
                'supplier' => new SupplierResource($supplier),
            ], ResponseMessage::UPDATED_SUPPLIER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_SUPPLIER->value);
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
            Supplier::findOrFail($supplier_id)->delete();

            return ResponseUtils::noContent(ResponseMessage::DELETED_SUPPLIER->value);
        } catch (ModelNotFoundException) {
            return ResponseUtils::notFound(ResponseMessage::NOT_FOUND_PUBLISHER->value);
        }
    }
}
