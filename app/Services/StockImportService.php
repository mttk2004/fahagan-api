<?php

namespace App\Services;

use App\Actions\StockImport\CreateStockImportAction;
use App\DTOs\StockImportDTO;
use App\Models\StockImport;

class StockImportService extends BaseService
{
    /**
     * StockImportService constructor.
     */
    public function __construct(
        private readonly CreateStockImportAction $createStockImportAction,
        // protected Model $model = new StockImport,
        // protected string $filterClass = StockImportFilter::class,
        // protected string $sortClass = StockImportSort::class,
        protected array $with = ['items']
    ) {
    }

    public function createStockImport(StockImportDTO $stockImportDTO): StockImport
    {
        return $this->createStockImportAction->execute($stockImportDTO, $this->with);
    }
}
