<?php

namespace App\Actions\StockImport;

use App\Actions\BaseAction;
use App\Models\Book;
use App\Models\StockImport;
use App\Utils\AuthUtils;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateStockImportAction extends BaseAction
{
  /**
   * Tạo phiếu nhập kho mới
   *
   * @param mixed ...$args
   * @return StockImport
   * @throws Throwable
   */
  public function execute(...$args): StockImport
  {
    [$stockImportDTO, $relations] = $args;

    DB::beginTransaction();

    try {
      // Tạo phiếu nhập kho
      $stockImport = StockImport::create([
        'employee_id' => AuthUtils::user()->id,
        'supplier_id' => $stockImportDTO->supplier_id,
        'discount_value' => $stockImportDTO->discount_value,
      ]);

      // Tạo các mục trong phiếu nhập kho
      foreach ($stockImportDTO->items as $item) {
        $stockImport->items()->create($item->toArray());
      }

      // Cập nhật số lượng sách trong kho
      foreach ($stockImportDTO->items as $item) {
        $book = Book::find($item->book_id);
        $book->available_count += $item->quantity;
        $book->save();
      }

      // Lấy order với các mối quan hệ
      return ! empty($relations) ? $stockImport->fresh($relations) : $stockImport->fresh(['employee', 'supplier']);
    } catch (Throwable $e) {
      DB::rollBack();

      throw $e;
    }
  }
}
