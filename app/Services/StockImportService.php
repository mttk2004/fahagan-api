<?php

namespace App\Services;

use App\Actions\StockImport\CreateStockImportAction;
use App\Constants\ApplicationConstants;
use App\DTOs\StockImportDTO;
use App\Http\Filters\V1\StockImportFilter;
use App\Http\Sorts\V1\StockImportSort;
use App\Models\StockImport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StockImportService extends BaseService
{
    /**
     * StockImportService constructor.
     */
    public function __construct(
        private readonly CreateStockImportAction $createStockImportAction,
    ) {
        $this->model = new StockImport;
        $this->filterClass = StockImportFilter::class;
        $this->sortClass = StockImportSort::class;
        $this->with = ['items'];
    }

    /**
     * Get all stock imports with pagination and filtering
     */
    public function getAllStockImports(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
    {
        return $this->getAll($request, $perPage);
    }

    /**
     * Create a new stock import
     */
    public function createStockImport(StockImportDTO $stockImportDTO): StockImport
    {
        return $this->createStockImportAction->execute($stockImportDTO, $this->with);
    }
}
