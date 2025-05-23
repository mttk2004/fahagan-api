<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\Models\Book;

class FindTrashedBookAction extends BaseAction
{
    /**
     * Tìm sách đã bị xóa mềm với title và edition cụ thể
     *
     * @param mixed ...$args
     *
     * @return Book|null
     */
    public function execute(...$args): ?Book
    {
        [$bookDTO] = $args;

        return Book::withTrashed()
            ->where('title', $bookDTO->title)
            ->where('edition', $bookDTO->edition)
            ->onlyTrashed()
            ->first();
    }
}
