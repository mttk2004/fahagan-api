<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use ErrorException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandleGenreExceptions
{
    /**
     * Xử lý các ngoại lệ liên quan đến Genre
     *
     * @param Exception $e Exception cần xử lý
     * @param array $requestData Dữ liệu từ request (đã validate)
     * @param string|int|null $genreId ID của thể loại (nếu có)
     * @param string $action Hành động đang thực hiện (tạo/cập nhật)
     * @return JsonResponse
     */
    protected function handleGenreException(
        Exception $e,
        array $requestData,
        string|int|null $genreId = null,
        string $action = 'tạo'
    ): JsonResponse {
        // Xử lý ValidationException từ GenreService
        if ($e instanceof ValidationException) {
            Log::info("Lỗi validation từ GenreService khi {$action} thể loại: " . $e->getMessage());

            return ResponseUtils::badRequest(
                'Dữ liệu không hợp lệ.',
                $e->errors()
            );
        }

        // Xử lý lỗi ràng buộc unique
        if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            Log::info("Lỗi ràng buộc unique khi {$action} thể loại: " . $e->getMessage());

            // Kiểm tra xem lỗi liên quan đến name
            if (strpos($e->getMessage(), 'genres_name_unique') !== false) {
                return ResponseUtils::badRequest(
                    'Tên thể loại đã tồn tại trong hệ thống.',
                    ['name' => 'Tên thể loại đã được sử dụng.']
                );
            }

            // Kiểm tra xem lỗi liên quan đến slug
            if (strpos($e->getMessage(), 'genres_slug_unique') !== false) {
                return ResponseUtils::badRequest(
                    'Slug đã tồn tại trong hệ thống.',
                    ['slug' => 'Slug đã được sử dụng.']
                );
            }

            return ResponseUtils::badRequest(
                'Lỗi ràng buộc dữ liệu: Thông tin cập nhật vi phạm ràng buộc duy nhất trong hệ thống.',
                ['unique' => 'Dữ liệu đã tồn tại trong hệ thống.']
            );
        }

        // Bắt lỗi PDOException (cha của UniqueConstraintViolationException)
        if ($e instanceof \PDOException) {
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'genres_name_unique') !== false) {
                    return ResponseUtils::badRequest(
                        'Tên thể loại đã tồn tại trong hệ thống.',
                        ['name' => 'Tên thể loại đã được sử dụng.']
                    );
                }

                if (strpos($e->getMessage(), 'genres_slug_unique') !== false) {
                    return ResponseUtils::badRequest(
                        'Slug đã tồn tại trong hệ thống.',
                        ['slug' => 'Slug đã được sử dụng.']
                    );
                }
            }

            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($genreId) {
                $logData['genre_id'] = $genreId;
            }

            Log::error("Lỗi PDO khi {$action} thể loại: " . $e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi {$action} thể loại. Vui lòng thử lại sau.");
        }

        // Xử lý ErrorException
        if ($e instanceof ErrorException) {
            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($genreId) {
                $logData['genre_id'] = $genreId;
            }

            Log::error("Lỗi khi {$action} thể loại: " . $e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi {$action} thể loại. Vui lòng thử lại sau.");
        }

        // Xử lý các Exception còn lại
        $logData = [
            'exception' => $e,
            'request' => $requestData,
        ];

        if ($genreId) {
            $logData['genre_id'] = $genreId;
        }

        Log::error("Lỗi không xác định khi {$action} thể loại: " . $e->getMessage(), $logData);

        return ResponseUtils::serverError("Đã xảy ra lỗi không xác định. Vui lòng thử lại sau.");
    }
}
