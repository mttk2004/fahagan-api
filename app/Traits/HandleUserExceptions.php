<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use ErrorException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandleUserExceptions
{
    /**
     * Xử lý các ngoại lệ liên quan đến User
     *
     * @param Exception $e Exception cần xử lý
     * @param array $requestData Dữ liệu từ request (đã validate)
     * @param string|int|null $userId ID của người dùng (nếu có)
     * @param string $action Hành động đang thực hiện (tạo/cập nhật)
     * @return JsonResponse
     */
    protected function handleUserException(
        Exception $e,
        array $requestData,
        string|int|null $userId = null,
        string $action = 'tạo'
    ): JsonResponse {
        // Xử lý ValidationException từ UserService
        if ($e instanceof ValidationException) {
            Log::info("Lỗi validation từ UserService khi {$action} người dùng: " . $e->getMessage());

            return ResponseUtils::badRequest(
                'Dữ liệu không hợp lệ.',
                $e->errors()
            );
        }

        // Xử lý lỗi ràng buộc unique
        if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            Log::info("Lỗi ràng buộc unique khi {$action} người dùng: " . $e->getMessage());

            // Kiểm tra xem lỗi liên quan đến email
            if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                return ResponseUtils::badRequest(
                    'Email đã tồn tại trong hệ thống.',
                    ['email' => 'Email đã được sử dụng.']
                );
            }

            // Kiểm tra xem lỗi liên quan đến phone
            if (strpos($e->getMessage(), 'users_phone_unique') !== false) {
                return ResponseUtils::badRequest(
                    'Số điện thoại đã tồn tại trong hệ thống.',
                    ['phone' => 'Số điện thoại đã được sử dụng.']
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
                if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                    return ResponseUtils::badRequest(
                        'Email đã tồn tại trong hệ thống.',
                        ['email' => 'Email đã được sử dụng.']
                    );
                }

                if (strpos($e->getMessage(), 'users_phone_unique') !== false) {
                    return ResponseUtils::badRequest(
                        'Số điện thoại đã tồn tại trong hệ thống.',
                        ['phone' => 'Số điện thoại đã được sử dụng.']
                    );
                }
            }

            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($userId) {
                $logData['user_id'] = $userId;
            }

            Log::error("Lỗi PDO khi {$action} người dùng: " . $e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi {$action} người dùng. Vui lòng thử lại sau.");
        }

        // Xử lý ErrorException
        if ($e instanceof ErrorException) {
            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($userId) {
                $logData['user_id'] = $userId;
            }

            Log::error("Lỗi khi {$action} người dùng: " . $e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi {$action} người dùng. Vui lòng thử lại sau.");
        }

        // Xử lý các Exception còn lại
        $logData = [
            'exception' => $e,
            'request' => $requestData,
        ];

        if ($userId) {
            $logData['user_id'] = $userId;
        }

        Log::error("Lỗi không xác định khi {$action} người dùng: " . $e->getMessage(), $logData);

        return ResponseUtils::serverError("Đã xảy ra lỗi không xác định. Vui lòng thử lại sau.");
    }
}
