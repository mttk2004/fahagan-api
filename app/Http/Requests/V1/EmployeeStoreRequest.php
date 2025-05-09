<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class EmployeeStoreRequest extends BaseRequest implements HasValidationMessages
{
  public function rules(): array
  {
    return [
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/', 'unique:users,phone'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:8'],
      'role' => ['required', 'string', 'in:Admin,Warehouse Staff,Sales Staff'],
    ];
  }

  public function messages(): array
  {
    return [
      'first_name.required' => 'Tên nhân viên là bắt buộc.',
      'first_name.string' => 'Tên nhân viên phải là một chuỗi.',
      'first_name.max' => 'Tên nhân viên không được vượt quá 255 ký tự.',
      'last_name.required' => 'Họ nhân viên là bắt buộc.',
      'last_name.string' => 'Họ nhân viên phải là một chuỗi.',
      'last_name.max' => 'Họ nhân viên không được vượt quá 255 ký tự.',
      'phone.required' => 'Số điện thoại là bắt buộc.',
      'phone.string' => 'Số điện thoại phải là một chuỗi.',
      'phone.regex' => 'Số điện thoại không hợp lệ.',
      'phone.unique' => 'Số điện thoại đã tồn tại.',
      'email.required' => 'Email là bắt buộc.',
      'email.email' => 'Email không hợp lệ.',
      'email.max' => 'Email không được vượt quá 255 ký tự.',
      'email.unique' => 'Email đã tồn tại.',
      'password.required' => 'Mật khẩu là bắt buộc.',
      'password.string' => 'Mật khẩu phải là một chuỗi.',
      'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
      'role.required' => 'Vai trò là bắt buộc.',
      'role.string' => 'Vai trò phải là một chuỗi.',
      'role.in' => 'Vai trò không hợp lệ.',
    ];
  }

  public function authorize(): bool
  {
    return AuthUtils::user()->hasRole('Admin');
  }

  public function bodyParameters(): array
  {
    return [
      'first_name' => [
        'description' => 'Tên nhân viên',
        'example' => 'John',
      ],
      'last_name' => [
        'description' => 'Họ nhân viên',
        'example' => 'Doe',
      ],
      'phone' => [
        'description' => 'Số điện thoại',
        'example' => '0909090909',
      ],
      'email' => [
        'description' => 'Email',
        'example' => 'john.doe@example.com',
      ],
      'password' => [
        'description' => 'Mật khẩu',
        'example' => 'password123',
      ],
      'role' => [
        'description' => 'Vai trò',
        'example' => 'Admin',
      ],
    ];
  }
}
