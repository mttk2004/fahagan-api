<?php

namespace App\Traits;

use App\Enums\ResponseMessage;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandleExceptions
{
  /**
   * Xử lý các ngoại lệ phổ biến trong controller
   *
   * @param Exception $e Exception cần xử lý
   * @param string $entityName Tên của entity (VD: 'publisher', 'author', 'book', etc.)
   * @param array $logData Dữ liệu bổ sung để log (optional)
   * @return JsonResponse
   */
  protected function handleException(
    Exception $e,
    string $entityName,
    array $logData = []
  ): JsonResponse {
    // Xử lý ModelNotFoundException
    if ($e instanceof ModelNotFoundException) {
      $notFoundMessage = $this->getNotFoundMessageForEntity($entityName);
      return ResponseUtils::notFound($notFoundMessage);
    }

    // Xử lý ValidationException
    if ($e instanceof ValidationException) {
      Log::info("Lỗi validation cho {$entityName}: " . $e->getMessage(), $logData);
      return ResponseUtils::validationError('Dữ liệu không hợp lệ.', $e->errors());
    }

    // Xử lý các Exception còn lại
    $logContext = array_merge(['exception' => get_class($e)], $logData);
    Log::error("Lỗi xử lý {$entityName}: " . $e->getMessage(), $logContext);

    return ResponseUtils::serverError($e->getMessage());
  }

  /**
   * Lấy thông báo not found cho từng loại entity
   *
   * @param string $entityName Tên của entity
   * @return string
   */
  private function getNotFoundMessageForEntity(string $entityName): string
  {
    return match ($entityName) {
      'publisher' => ResponseMessage::NOT_FOUND_PUBLISHER->value,
      'author' => ResponseMessage::NOT_FOUND_AUTHOR->value,
      'book' => ResponseMessage::NOT_FOUND_BOOK->value,
      'genre' => ResponseMessage::NOT_FOUND_GENRE->value,
      'supplier' => ResponseMessage::NOT_FOUND_SUPPLIER->value,
      'user' => ResponseMessage::NOT_FOUND_USER->value,
      'discount' => ResponseMessage::NOT_FOUND_DISCOUNT->value,
      default => "Không tìm thấy {$entityName}.",
    };
  }
}
