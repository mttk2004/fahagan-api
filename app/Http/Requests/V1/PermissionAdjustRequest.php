<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class PermissionAdjustRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'permissions' => ['required', 'array'],
          'permissions.*' => ['required', 'string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
          'permissions.required' => 'Quyền là trường bắt buộc.',
          'permissions.array' => 'Quyền nên là một mảng.',
          'permissions.*.required' => 'Quyền là trường bắt buộc.',
          'permissions.*.string' => 'Quyền nên là một chuỗi.',
          'permissions.*.exists' => 'Quyền không tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::user()->hasRole('Admin');
    }

    public function bodyParameters(): array
    {
        return [
          'permissions' => [
            'description' => 'Quyền của người nhận',
            'example' => '["view_books", "edit_books"]',
          ],
        ];
    }
}
