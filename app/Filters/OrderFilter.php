<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderFilter
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

    $this->filterByStatus($query);
    $this->filterByShoppingAddress($query);
    $this->filterByCustomer($query);

    return $query;
  }

  protected function filterByStatus(Builder $query): void
  {
    if (isset($this->filters['status'])) {
      $status = $this->filters['status'];
      $query->where(function ($q) use ($status) {
        $q->where('status', '=', $status);
      });
    }
  }

  protected function filterByShoppingAddress(Builder $query): void
  {
    if (isset($this->filters['city'])) {
      $query->where('shopping_city', 'like', '%' . $this->filters['city'] . '%');
    }

    if (isset($this->filters['district'])) {
      $query->where('shopping_district', 'like', '%' . $this->filters['district'] . '%');
    }

    if (isset($this->filters['ward'])) {
      $query->where('shopping_ward', 'like', '%' . $this->filters['ward'] . '%');
    }

    if (isset($this->filters['address_line'])) {
      $query->where('shopping_address_line', 'like', '%' . $this->filters['address'] . '%');
    }

    if (isset($this->filters['phone'])) {
      $query->where('shopping_phone', 'like', '%' . $this->filters['phone'] . '%');
    }
  }

  protected function filterByCustomer(Builder $query): void
  {
    if (isset($this->filters['customer_id'])) {
      $query->where('user_id', $this->filters['customer_id']);
    }
  }
}
