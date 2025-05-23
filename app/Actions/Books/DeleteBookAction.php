<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteBookAction extends BaseAction
{
    /**
     * Xóa sách (soft delete)
     *
     * @param mixed ...$args
     *
     * @return Book Sách đã xóa
     *
     * @throws Throwable
     */
    public function execute(...$args): Book
    {
        [$book] = $args;

        DB::beginTransaction();

        try {
            // Xóa các mối quan hệ discount liên quan đến sách này
            $book->getActiveDiscounts()->each(function ($discount) use ($book) {
                $discount->targets()->where('target_id', $book->id)->delete();
            });

            // Soft delete sách
            $book->delete();

            DB::commit();

            return $book;
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
