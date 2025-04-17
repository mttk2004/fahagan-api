<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'old_password' => 'required|string|min:8',
          'new_password' => [
            'required',
            'string',
            Password::default(),
            'different:old_password',
            'confirmed',
          ],
        ];
    }

    public function messages(): array
    {
        return [
          'old_password.required' => 'Mật khẩu cũ là trường bắt buộc.',
          'old_password.string' => 'Mật khẩu cũ nên là một chuỗi.',
          'old_password.min' => 'Mật khẩu cũ nên có ít nhất 8 ký tự.',

          'new_password.required' => 'Mật khẩu mới là trường bắt buộc.',
          'new_password.string' => 'Mật khẩu mới nên là một chuỗi.',
          'new_password.different' => 'Mật khẩu mới phải khác mật khẩu cũ.',
          'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }

    public function authorize(): bool
    {
        return true;
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
