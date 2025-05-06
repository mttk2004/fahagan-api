<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use ErrorException;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;

trait HandleBookExceptions
{
    /**
     * Xử lý các ngoại lệ liên quan đến Book
     *
     * @param  Exception  $e  Exception cần xử lý
     * @param  array  $requestData  Dữ liệu từ request (đã validate)
     * @param  string|int|null  $bookId  ID của sách (nếu có)
     * @param  string  $action  Hành động đang thực hiện (tạo/cập nhật)
     */
    protected function handleBookException(
        Exception $e,
        array $requestData,
        string|int|null $bookId = null,
        string $action = 'tạo'
    ): JsonResponse {
        // Xử lý ValidationException từ BookService
        if ($e instanceof ValidationException) {
            Log::info("Lỗi validation từ BookService khi $action sách: ".$e->getMessage());

            return ResponseUtils::validationError(
                'Dữ liệu không hợp lệ.',
                $e->errors()
            );
        }

        // Xử lý lỗi ràng buộc unique
        if ($e instanceof UniqueConstraintViolationException) {
            Log::info("Lỗi ràng buộc unique khi $action sách: ".$e->getMessage());

            // Kiểm tra xem lỗi liên quan đến title-edition hay không
            if (str_contains($e->getMessage(), 'books_title_edition_unique')) {
                return ResponseUtils::validationError(
                    'Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.',
                    ['title_edition' => 'Tiêu đề và phiên bản phải là duy nhất.']
                );
            }

            return ResponseUtils::validationError(
                'Lỗi ràng buộc dữ liệu: Thông tin cập nhật vi phạm ràng buộc duy nhất trong hệ thống.',
                ['unique' => 'Dữ liệu đã tồn tại trong hệ thống.']
            );
        }

        // Bắt lỗi PDOException (cha của UniqueConstraintViolationException)
        if ($e instanceof PDOException) {
            if ($e->getCode() == 23000 && str_contains(
                $e->getMessage(),
                'books_title_edition_unique'
            )) {
                return ResponseUtils::validationError(
                    'Đã tồn tại sách với tiêu đề và phiên bản này. Vui lòng sử dụng tiêu đề hoặc phiên bản khác.',
                    ['title_edition' => 'Tiêu đề và phiên bản phải là duy nhất.']
                );
            }

            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($bookId) {
                $logData['book_id'] = $bookId;
            }

            Log::error("Lỗi PDO khi $action sách: ".$e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi $action sách. Vui lòng thử lại sau.");
        }

        // Xử lý ErrorException
        if ($e instanceof ErrorException) {
            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($bookId) {
                $logData['book_id'] = $bookId;
            }

            Log::error("Lỗi khi $action sách: ".$e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi $action sách. Vui lòng thử lại sau.");
        }

        // Xử lý các Exception còn lại
        $logData = [
            'exception' => $e,
            'request' => $requestData,
        ];

        if ($bookId) {
            $logData['book_id'] = $bookId;
        }

        Log::error("Lỗi không xác định khi $action sách: ".$e->getMessage(), $logData);

        return ResponseUtils::serverError('Đã xảy ra lỗi không xác định. Vui lòng thử lại sau.');
    }
}
