<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
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
        $this->filterByPhone($query);
        $this->filterByCustomer($query);

        return $query;
    }

    protected function filterByName(Builder $query): void
    {
        if (isset($this->filters['name'])) {
            $name = $this->filters['name'];
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            });
        }
    }

    protected function filterByEmail(Builder $query): void
    {
        if (isset($this->filters['email'])) {
            $query->where('email', 'like', '%'.$this->filters['email'].'%');
        }
    }

    protected function filterByPhone(Builder $query): void
    {
        if (isset($this->filters['phone'])) {
            $query->where('phone', 'like', '%'.$this->filters['phone'].'%');
        }
    }

    protected function filterByCustomer(Builder $query): void
    {
        if (isset($this->filters['is_customer'])) {
            $isCustomer = filter_var($this->filters['is_customer'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_customer', $isCustomer);
        }
    }
}
