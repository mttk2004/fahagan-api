<?php

namespace App\Http\Sorts\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GenreSort
{
    protected Request $request;

    protected array $sorts;

    protected array $validSorts = [
        'id',
        'name',
        'slug',
        'created_at',
        'updated_at',
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->sorts = $request->get('sort', []);
    }

    public function apply(Builder $query): Builder
    {
        if (empty($this->sorts)) {
            return $query->orderBy('name', 'asc');
        }

        $sorts = $this->parseSorts();

        foreach ($sorts as $sort) {
            $column = $sort['column'];
            $direction = $sort['direction'];

            if (in_array($column, $this->validSorts)) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    protected function parseSorts(): array
    {
        if (is_string($this->sorts)) {
            $this->sorts = explode(',', $this->sorts);
        }

        $sorts = [];

        foreach ($this->sorts as $sort) {
            $direction = 'asc';

            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            $sorts[] = [
                'column' => $sort,
                'direction' => $direction,
            ];
        }

        return $sorts;
    }
}
