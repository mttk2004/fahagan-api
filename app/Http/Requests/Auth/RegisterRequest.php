<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class RegisterRequest extends BaseRequest implements HasValidationMessages
{
  public function rules(): array
  {
    return [
      'first_name' => ['required', 'string', 'max:30'],
      'last_name' => ['required', 'string', 'max:30'],
      'phone' => [
        'required',
        'string',
        'regex:/^0[35789][0-9]{8}$/',
        'unique:users,phone',
      ],
      'email' => [
        'required',
        'string',
        'email',
        'max:50',
        'unique:users,email',
      ],
      'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'first_name.required' => 'Tên là trường bắt buộc.',
      'first_name.string' => 'Tên nên là một chuỗi.',
      'first_name.max' => 'Tên nên có độ dài tối đa 30.',

      'last_name.required' => 'Họ là trường bắt buộc.',
      'last_name.string' => 'Họ nên là một chuỗi.',
      'last_name.max' => 'Họ nên có độ dài tối đa 30.',

      'phone.required' => 'Số điện thoại là trường bắt buộc.',
      'phone.string' => 'Số điện thoại nên là một chuỗi.',
      'phone.regex' => 'Số điện thoại không hợp lệ.',
      'phone.unique' => 'Số điện thoại đã được sử dụng.',

      'email.required' => 'Email là trường bắt buộc.',
      'email.string' => 'Email nên là một chuỗi.',
      'email.email' => 'Email không hợp lệ.',
      'email.max' => 'Email nên có độ dài tối đa 50.',
      'email.unique' => 'Email đã được sử dụng.',

      'password.required' => 'Mật khẩu là trường bắt buộc.',
      'password.string' => 'Mật khẩu nên là một chuỗi.',
      'password.min' => 'Mật khẩu nên có ít nhất 8 ký tự.',
      'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
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
