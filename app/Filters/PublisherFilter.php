<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublisherFilter
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
        $this->filterByBooks($query);

        return $query;
    }

    protected function filterByName(Builder $query): void
    {
        if (isset($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }
    }

    protected function filterByBooks(Builder $query): void
    {
        if (isset($this->filters['books'])) {
            $bookIds = explode(',', $this->filters['books']);
            $query->whereHas('publishedBooks', function ($q) use ($bookIds) {
                $q->whereIn('books.id', $bookIds);
            });
        }
    }
}
