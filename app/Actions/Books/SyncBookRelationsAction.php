<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\Models\Book;

class SyncBookRelationsAction extends BaseAction
{
  /**
   * Đồng bộ các mối quan hệ với sách
   *
   * @param Book $book Sách cần đồng bộ mối quan hệ
   * @param array $relations Mảng chứa các mối quan hệ cần đồng bộ
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
   *
   * @param array $requestData
   * @return array
   */
  public function extractRelationsFromRequest(array $requestData): array
  {
    $relations = [];

    // Trích xuất mối quan hệ authors nếu có
    $authors = data_get($requestData, 'data.relationships.authors.data');
    if (!empty($authors)) {
      $relations['authors'] = collect($authors)->pluck('id')->toArray();
    }

    // Trích xuất mối quan hệ genres nếu có
    $genres = data_get($requestData, 'data.relationships.genres.data');
    if (!empty($genres)) {
      $relations['genres'] = collect($genres)->pluck('id')->toArray();
    }

    // Trích xuất mối quan hệ publisher nếu có
    $publisher = data_get($requestData, 'data.relationships.publisher.data.id');
    if (!empty($publisher)) {
      $relations['publisher'] = $publisher;
    }

    return $relations;
  }
}
