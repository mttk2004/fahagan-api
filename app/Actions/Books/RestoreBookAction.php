<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\DTOs\Book\BookDTO;
use App\Models\Book;
use Exception;
use Illuminate\Support\Facades\DB;

class RestoreBookAction extends BaseAction
{
    /**
     * Khôi phục sách đã bị xóa mềm và cập nhật thông tin mới
     *
     * @param  Book  $trashedBook  Sách đã bị xóa mềm
     * @param  BookDTO  $bookDTO  Dữ liệu để cập nhật
     *
     * @throws Exception
     */
    public function execute(...$args): Book
    {
        [$trashedBook, $bookDTO] = $args;

        DB::beginTransaction();

        try {
            // Khôi phục sách
            $trashedBook->restore();

            // Cập nhật thông tin mới
            $trashedBook->update([
                'title' => $bookDTO->title,
                'edition' => $bookDTO->edition,
                'description' => $bookDTO->description,
                'price' => $bookDTO->price,
                'pages' => $bookDTO->pages ?? $bookDTO->number_of_pages ?? null,
                'image_url' => $bookDTO->image_url ?? $bookDTO->cover_image ?? null,
                'publication_date' => $bookDTO->publication_date ?? $bookDTO->published_date ?? null,
            ]);

            // Cập nhật mối quan hệ
            if (! empty($bookDTO->author_ids)) {
                $trashedBook->authors()->sync($bookDTO->author_ids);
            }

            if (! empty($bookDTO->genre_ids)) {
                $trashedBook->genres()->sync($bookDTO->genre_ids);
            }

            if (isset($bookDTO->publisher_id)) {
                $trashedBook->publisher()->associate($bookDTO->publisher_id);
                $trashedBook->save();
            }

            DB::commit();

            // Lấy sách với các mối quan hệ
            return $trashedBook->fresh(['authors', 'genres', 'publisher']);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
