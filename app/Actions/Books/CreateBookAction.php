<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;


class CreateBookAction extends BaseAction
{
    /**
     * Tạo sách mới
     *
     * @param mixed ...$args
     *
     * @return Book
     * @throws Throwable
     */
    public function execute(...$args): Book
    {
        [$bookDTO, $relations] = $args;

        DB::beginTransaction();

        try {
            // Tạo sách mới
            $book = Book::create([
                'title' => $bookDTO->title,
                'edition' => $bookDTO->edition,
                'description' => $bookDTO->description,
                'price' => $bookDTO->price,
                'pages' => $bookDTO->pages ?? $bookDTO->number_of_pages ?? null,
                'image_url' => $bookDTO->image_url ?? $bookDTO->cover_image ?? null,
                'publication_date' => $bookDTO->publication_date ?? $bookDTO->published_date ?? null,
                'publisher_id' => $bookDTO->publisher_id,
            ]);

            // Thiết lập mối quan hệ authors nếu có
            if (! empty($bookDTO->author_ids)) {
                $book->authors()->sync($bookDTO->author_ids);
            }

            // Thiết lập mối quan hệ genres nếu có
            if (! empty($bookDTO->genre_ids)) {
                $book->genres()->sync($bookDTO->genre_ids);
            }

            DB::commit();

            // Lấy sách với các mối quan hệ
            return ! empty($relations) ? $book->fresh($relations) : $book->fresh(['authors', 'genres', 'publisher']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
