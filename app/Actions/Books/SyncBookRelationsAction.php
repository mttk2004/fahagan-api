<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;

class SyncBookRelationsAction extends BaseAction
{
    /**
     * Đồng bộ các mối quan hệ với sách
     *
     * @param mixed ...$args
     * @return bool
     */
    public function execute(...$args): bool
    {
        [$book, $relations] = $args;

        // Đồng bộ mối quan hệ authors nếu có
        if (isset($relations['authors'])) {
            $book->authors()->sync($relations['authors']);
        }

        // Đồng bộ mối quan hệ genres nếu có
        if (isset($relations['genres'])) {
            $book->genres()->sync($relations['genres']);
        }

        // Cập nhật mối quan hệ publisher nếu có
        if (isset($relations['publisher'])) {
            $book->publisher()->associate($relations['publisher']);
            $book->save();
        }

        return true;
    }

    /**
     * Trích xuất các mối quan hệ từ request data
     */
    public function extractRelationsFromRequest(array $requestData): array
    {
        $relations = [];

        // Thử lấy dữ liệu theo cả hai định dạng (cũ và mới)
        // Định dạng cũ: data.relationships.authors.data[].id
        // Định dạng mới: data.relationships.authors[].id

        // Trích xuất mối quan hệ authors
        $authorIds = [];

        // Thử cấu trúc mới trước (đơn giản hơn)
        $authors = data_get($requestData, 'data.relationships.authors');
        if (! empty($authors)) {
            // Xử lý cho trường hợp cấu trúc đơn giản hơn (data.relationships.authors)
            if (is_array($authors)) {
                // Trường hợp mảng các authors có id trực tiếp
                $authorIds = collect($authors)->pluck('id')->filter()->values()->toArray();
            }
        }

        // Nếu không tìm thấy trong cấu trúc mới, thử cấu trúc cũ
        if (empty($authorIds)) {
            $authorsData = data_get($requestData, 'data.relationships.authors.data');
            if (! empty($authorsData)) {
                $authorIds = collect($authorsData)->pluck('id')->filter()->values()->toArray();
            }
        }

        if (! empty($authorIds)) {
            $relations['authors'] = $authorIds;
        }

        // Trích xuất mối quan hệ genres
        $genreIds = [];

        // Thử cấu trúc mới trước (đơn giản hơn)
        $genres = data_get($requestData, 'data.relationships.genres');
        if (! empty($genres)) {
            // Xử lý cho trường hợp cấu trúc đơn giản hơn (data.relationships.genres)
            if (is_array($genres)) {
                // Trường hợp mảng các genres có id trực tiếp
                $genreIds = collect($genres)->pluck('id')->filter()->values()->toArray();
            }
        }

        // Nếu không tìm thấy trong cấu trúc mới, thử cấu trúc cũ
        if (empty($genreIds)) {
            $genresData = data_get($requestData, 'data.relationships.genres.data');
            if (! empty($genresData)) {
                $genreIds = collect($genresData)->pluck('id')->filter()->values()->toArray();
            }
        }

        if (! empty($genreIds)) {
            $relations['genres'] = $genreIds;
        }

        // Trích xuất mối quan hệ publisher
        $publisher = data_get($requestData, 'data.relationships.publisher.id');
        if (! empty($publisher)) {
            $relations['publisher'] = $publisher;
        }

        return $relations;
    }
}
