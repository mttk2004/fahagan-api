<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class DiscountFilter
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

        $this->filterByName($query);
        $this->filterByType($query);
        $this->filterByValue($query);
        $this->filterByDate($query);

        return $query;
    }

    protected function filterByName(Builder $query): void
    {
        if (isset($this->filters['name'])) {
            $query->where('name', 'like', '%'.$this->filters['name'].'%');
        }
    }

    protected function filterByType(Builder $query): void
    {
        if (isset($this->filters['discount_type'])) {
            $query->where('discount_type', $this->filters['discount_type']);
        }
    }

    protected function filterByValue(Builder $query): void
    {
        if (isset($this->filters['discount_value'])) {
            $query->where('discount_value', '>=', $this->filters['discount_value']);
        }
    }

    protected function filterByDate(Builder $query): void
    {
        if (isset($this->filters['start_date'])) {
            $query->where('start_date', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('end_date', '<=', $this->filters['end_date']);
        }
    }
}
