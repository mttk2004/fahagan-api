<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class LoginRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'email' => [
            'required',
            'string',
            'email',
            'max:50',
          ],
          'password' => [
            'required',
            'string',
            'min:8',
          ],
        ];
    }

    public function messages(): array
    {
        return [
          'email.required' => 'Email là trường bắt buộc.',
          'email.string' => 'Email nên là một chuỗi.',
          'email.email' => 'Email không hợp lệ.',
          'email.max' => 'Email nên có độ dài tối đa 50.',

          'password.required' => 'Mật khẩu là trường bắt buộc.',
          'password.string' => 'Mật khẩu nên là một chuỗi.',
          'password.min' => 'Mật khẩu nên có ít nhất 8 ký tự.',
        ];
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
      ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
