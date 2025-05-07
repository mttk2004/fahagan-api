<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class SupplierFilter
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
        $this->filterByEmail($query);

        return $query;
    }

    protected function filterByName(Builder $query): void
    {
        if (isset($this->filters['name'])) {
            $query->where('name', 'like', '%'.$this->filters['name'].'%');
        }
    }

    protected function filterByEmail(Builder $query): void
    {
        if (isset($this->filters['email'])) {
            $query->where('email', 'like', '%'.$this->filters['email'].'%');
        }
    }
}
