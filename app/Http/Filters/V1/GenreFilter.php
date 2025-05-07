<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GenreFilter
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
        $this->filterBySlug($query);

        return $query;
    }

    protected function filterByName(Builder $query): void
    {
        if (isset($this->filters['name'])) {
            $query->where('name', 'like', '%'.$this->filters['name'].'%');
        }
    }

    protected function filterBySlug(Builder $query): void
    {
        if (isset($this->filters['slug'])) {
            $query->where('slug', 'like', '%'.$this->filters['slug'].'%');
        }
    }
}
