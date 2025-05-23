<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateBookAction extends BaseAction
{
    /**
     * Cập nhật sách với dữ liệu và mối quan hệ mới
     *
     * @param mixed ...$args
     *
     * @return Book
     * @throws Throwable
     */
    public function execute(...$args): Book
    {
        [$book, $data, $relations, $with] = $args;

        DB::beginTransaction();

        try {
            // Cập nhật thông tin sách nếu có
            if (! empty($data)) {
                $book->update($data);
            }

            // Cập nhật mối quan hệ authors nếu có
            if (isset($relations['authors'])) {
                $book->authors()->sync($relations['authors']);
            }

            // Cập nhật mối quan hệ genres nếu có
            if (isset($relations['genres'])) {
                $book->genres()->sync($relations['genres']);
            }

            // Cập nhật mối quan hệ publisher nếu có
            if (isset($relations['publisher'])) {
                $book->publisher()->associate($relations['publisher']);
                $book->save();
            }

            DB::commit();

            // Lấy sách với các mối quan hệ đã được cập nhật
            return ! empty($with) ? $book->fresh($with) : $book->fresh(['authors', 'genres', 'publisher']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
