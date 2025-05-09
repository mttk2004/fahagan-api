<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\StockImportDTO;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StockImportStoreRequest;
use App\Http\Resources\V1\StockImportCollection;
use App\Http\Resources\V1\StockImportResource;
use App\Services\StockImportService;
use App\Traits\HandleExceptions;
use App\Traits\HandlePagination;
use App\Traits\HandleValidation;
use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\Request;

class StockImportController extends Controller
{
    use HandleExceptions;
    use HandlePagination;
    use HandleValidation;

    public function __construct(
        private readonly StockImportService $stockImportService,
        private readonly string $entityName = 'stock_import'
    ) {
    }

    /**
     * Get all stock imports with pagination and filtering
     *
     * @param Request $request
     * @return JsonResponse
     * @group StockImport
     * @authenticated
     */
    public function index(Request $request)
    {
        $stockImports = $this->stockImportService->getAllStockImports(
            $request,
            $this->getPerPage($request)
        );

        return new StockImportCollection($stockImports);
    }

    /**
     * Create a new stock import
     *
     * @param StockImportStoreRequest $request
     * @return JsonResponse
     * @group StockImport
     * @authenticated
     */
    public function store(StockImportStoreRequest $request)
    {
        if (! AuthUtils::userCan('create_stock_imports')) {
            return ResponseUtils::forbidden();
        }

        try {
            $stockImport = $this->stockImportService->createStockImport(
                StockImportDTO::fromRequest($request->validated())
            );

            return ResponseUtils::success([
              'stock_import' => new StockImportResource($stockImport),
            ], ResponseMessage::STOCK_IMPORT_CREATED->value);
        } catch (Exception $e) {
            return $this->handleException($e, $this->entityName, [
              'request_data' => $request->validated(),
            ]);
        }
    }
}
