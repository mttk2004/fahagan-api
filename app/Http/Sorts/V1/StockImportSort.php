<?php

namespace App\Http\Sorts\V1;

class StockImportSort extends Sort
{
  protected array $sortableColumns
  = [
    'imported_at',
    'created_at',
    'updated_at',
  ];
}
