<?php

namespace App\Actions\Books;

use App\Actions\BaseAction;
use App\DTOs\Book\BookDTO;
use App\Models\Book;
use Illuminate\Validation\ValidationException;

class ValidateBookAction extends BaseAction
{
  /**
   * Xác thực dữ liệu sách
   *
   * @param BookDTO $bookDTO
   * @param bool $forUpdate Xác thực cho cập nhật hay tạo mới
   * @param Book|null $book Đối tượng Book hiện tại (chỉ khi $forUpdate = true)
   * @return bool
   * @throws ValidationException
   */
  public function execute(...$args): bool
  {
    if (count($args) === 1) {
      [$bookDTO] = $args;
      $forUpdate = false;
      $book = null;
    } else {
      [$bookDTO, $forUpdate, $book] = $args;
    }

    // Xác thực các trường bắt buộc
    if (!$forUpdate) {
      if (empty($bookDTO->title)) {
        throw ValidationException::withMessages(['title' => 'Tiêu đề sách là bắt buộc.']);
      }

      if (empty($bookDTO->edition)) {
        throw ValidationException::withMessages(['edition' => 'Phiên bản sách là bắt buộc.']);
      }
    }

    // Xác thực giá
    if (isset($bookDTO->price) && $bookDTO->price < 0) {
      throw ValidationException::withMessages(['price' => 'Giá sách không được âm.']);
    }

    // Xác thực sold_count
    if (isset($bookDTO->sold_count) && $bookDTO->sold_count < 0) {
      throw ValidationException::withMessages(['sold_count' => 'Số lượng sách đã bán không được âm.']);
    }

    // Xác thực available_count
    if (isset($bookDTO->available_count) && $bookDTO->available_count < 0) {
      throw ValidationException::withMessages(['available_count' => 'Số lượng sách không được âm.']);
    }

    // Xác thực trùng lặp nếu đang tạo mới hoặc thay đổi tiêu đề/phiên bản khi cập nhật
    if (!$forUpdate) {
      $this->validateBookDoesNotExist($bookDTO);
    } elseif ($book && ($bookDTO->title !== $book->title || $bookDTO->edition !== $book->edition)) {
      $title = $bookDTO->title ?? $book->title;
      $edition = $bookDTO->edition ?? $book->edition;

      $duplicate = Book::where('title', $title)
        ->where('edition', $edition)
        ->where('id', '!=', $book->id)
        ->first();

      if ($duplicate) {
        throw ValidationException::withMessages([
          'title' => 'Đã tồn tại sách với tiêu đề và phiên bản này.'
        ]);
      }
    }

    return true;
  }

  /**
   * Xác thực sách không tồn tại với cùng title và edition
   *
   * @param BookDTO $bookDTO
   * @throws ValidationException
   */
  private function validateBookDoesNotExist(BookDTO $bookDTO): void
  {
    $existingBook = Book::where('title', $bookDTO->title)
      ->where('edition', $bookDTO->edition)
      ->first();

    if ($existingBook) {
      throw ValidationException::withMessages([
        'title' => 'Đã tồn tại sách với tiêu đề và phiên bản này.'
      ]);
    }
  }
}
