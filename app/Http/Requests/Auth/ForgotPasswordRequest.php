<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class ForgotPasswordRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'email' => 'required|string|email|max:255|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
          'email.required' => 'Email là trường bắt buộc.',
          'email.email' => 'Email không hợp lệ.',
          'email.exists' => 'Email không tồn tại trong hệ thống.',
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
