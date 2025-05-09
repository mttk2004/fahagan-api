<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\Http\Filters\V1\BookFilter;
use App\Http\Sorts\V1\BookSort;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
  /**
   * Tìm kiếm sách với các tham số tùy chọn
   */
  public function searchBooks(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
  {
    $query = $request->get('query');
    $priceFrom = $request->get('price_from');
    $priceTo = $request->get('price_to');
    $genres = $request->get('genres');
    $authors = $request->get('authors');
    $publishers = $request->get('publishers');

    $bookQuery = Book::query()->with(['authors', 'genres', 'publisher']);

    // Tìm kiếm theo tên sách hoặc mô tả
    if ($query) {
      $bookQuery->where(function (Builder $q) use ($query) {
        $q->where('title', 'like', '%' . $query . '%')
          ->orWhere('description', 'like', '%' . $query . '%');
      });
    }

    // Lọc theo khoảng giá
    if ($priceFrom) {
      $bookQuery->where('price', '>=', $priceFrom);
    }

    if ($priceTo) {
      $bookQuery->where('price', '<=', $priceTo);
    }

    // Lọc theo thể loại
    if ($genres) {
      $genreIds = explode(',', $genres);
      $bookQuery->whereHas('genres', function (Builder $q) use ($genreIds) {
        $q->whereIn('genres.id', $genreIds);
      });
    }

    // Lọc theo tác giả
    if ($authors) {
      $authorIds = explode(',', $authors);
      $bookQuery->whereHas('authors', function (Builder $q) use ($authorIds) {
        $q->whereIn('authors.id', $authorIds);
      });
    }

    // Lọc theo nhà xuất bản
    if ($publishers) {
      $publisherIds = explode(',', $publishers);
      $bookQuery->whereIn('publisher_id', $publisherIds);
    }

    // Áp dụng sắp xếp nếu có
    if ($request->has('sort')) {
      $sort = new BookSort($request);
      $bookQuery = $sort->apply($bookQuery);
    } else {
      // Mặc định sắp xếp theo ngày tạo mới nhất
      $bookQuery->orderBy('created_at', 'desc');
    }

    return $bookQuery->paginate($perPage);
  }
}
