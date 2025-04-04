<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;

class ForgotPasswordRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['email']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.email' => 'required|string|email|max:255|exists:users,email',
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.email.required' => 'Email là trường bắt buộc.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.exists' => 'Email không tồn tại trong hệ thống.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
