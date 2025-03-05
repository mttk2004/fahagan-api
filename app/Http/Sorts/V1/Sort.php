<?php

namespace App\Http\Sorts\V1;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class Sort
{
	protected Request $request;
	protected array $sortableColumns = [];

	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function apply(Builder $builder): Builder
	{
		$sorts = explode(',', $this->request->input('sort', ''));
		if (empty($sorts)) {
			return $builder;
		}

		foreach ($sorts as $sortColumn) {
			$sortDirection = str_starts_with($sortColumn, '-') ? 'desc' : 'asc';
			$sortColumn = ltrim($sortColumn, '-');
			if (!empty($sortColumn) && in_array($sortColumn, $this->sortableColumns)) {
				$builder->orderBy($sortColumn, $sortDirection);
			}
		}

		return $builder;
	}
}
