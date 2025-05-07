<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class RoleAdjustRequest extends BaseRequest implements HasValidationMessages
{
  public function rules(): array
  {
    return [
      'roles' => ['required', 'array'],
      'roles.*' => ['required', 'string', 'exists:roles,name'],
    ];
  }

  public function messages(): array
  {
    return [
      'roles.required' => 'Vai trò là trường bắt buộc.',
      'roles.array' => 'Vai trò nên là một mảng.',
      'roles.*.required' => 'Vai trò là trường bắt buộc.',
      'roles.*.string' => 'Vai trò nên là một chuỗi.',
      'roles.*.exists' => 'Vai trò không tồn tại.',
    ];
  }

  public function authorize(): bool
  {
    return AuthUtils::user()->hasRole('Admin');
  }

  public function bodyParameters(): array
  {
    return [
      'roles' => [
        'description' => 'Vai trò của người nhận',
        'example' => '["Admin", "Sales Staff"]',
      ],
    ];
  }
}
