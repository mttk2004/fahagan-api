<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

abstract class BaseRequest extends FormRequest
{
  /**
   * Xác thực quyền truy cập
   * Nếu không có quyền, ném ra AuthorizationException
   *
   * @throws AuthorizationException
   * @return void
   */
  public function failedAuthorization()
  {
    throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
  }

  /**
   * Xử lý lỗi xác thực
   *
   * @param Validator $validator
   * @return void
   * @throws HttpResponseException
   */
  protected function failedValidation(Validator $validator): void
  {
    throw new HttpResponseException(
      response()->json([
        'message' => 'Dữ liệu không hợp lệ.',
        'errors' => $validator->errors(),
        'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
      ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
    );
  }

  /**
   * Tạo quy tắc unique đồng nhất, có hỗ trợ soft delete
   *
   * @param string $table Tên bảng cần kiểm tra
   * @param string $column Tên cột cần kiểm tra tính duy nhất
   * @param string|int|null $ignoredId ID của bản ghi cần bỏ qua (khi cập nhật)
   * @param bool $softDelete Có kiểm tra soft delete hay không
   * @param string $idColumn Tên cột ID
   * @param string $deletedAtColumn Tên cột deleted_at
   * @return string
   */
  protected function uniqueRule(
    string $table,
    string $column,
    $ignoredId = null,
    bool $softDelete = true,
    string $idColumn = 'id',
    string $deletedAtColumn = 'deleted_at'
  ): string {
    // Bắt đầu với quy tắc unique cơ bản
    $rule = "unique:{$table},{$column}";

    // Thêm ID cần bỏ qua nếu có
    if ($ignoredId !== null) {
      $rule .= ",{$ignoredId},{$idColumn}";
    } else {
      $rule .= ",NULL,{$idColumn}";
    }

    // Thêm điều kiện soft delete nếu cần
    if ($softDelete) {
      $rule .= ",{$deletedAtColumn},NULL";
    }

    return $rule;
  }

  /**
   * Kiểm tra dữ liệu cập nhật có rỗng không
   *
   * @param array $validatedData Dữ liệu đã xác thực
   * @return bool
   */
  protected function isEmptyUpdateData(array $validatedData): bool
  {
    // Kiểm tra theo định dạng JSON:API
    if (isset($validatedData['data'])) {
      return empty($validatedData['data']['attributes'] ?? [])
        && empty($validatedData['data']['relationships'] ?? []);
    }

    // Kiểm tra theo định dạng trực tiếp
    return empty($validatedData);
  }
}
