<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StockImportFilter
{
    protected Request $request;

    protected array $filters;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->filters = $request->get('filter', []);
    }

    public function apply(Builder $query): Builder
    {
        if (empty($this->filters)) {
            return $query;
        }

        $this->filterBySupplier($query);
        $this->filterByBooks($query);

        return $query;
    }

    protected function filterBySupplier(Builder $query): void
    {
        if (isset($this->filters['supplier'])) {
            $query->where('supplier_id', $this->filters['supplier']);
        }
    }

    protected function filterByBooks(Builder $query): void
    {
        if (isset($this->filters['books'])) {
            $bookIds = explode(',', $this->filters['books']);
            $query->whereHas('items', function ($q) use ($bookIds) {
                $q->whereIn('book_id', $bookIds);
            });
        }
    }
}
