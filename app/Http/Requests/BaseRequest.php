<?php

namespace App\Http\Requests;

use App\Enums\ResponseMessage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

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
    throw new AuthorizationException(ResponseMessage::FORBIDDEN->value);
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
        'message' => ResponseMessage::VALIDATION_ERROR,
        'errors' => $validator->errors(),
        'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
      ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
    );
  }
}
