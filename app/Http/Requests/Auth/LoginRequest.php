<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Arr;

class LoginRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        $this->convertToJsonApiFormat(['email', 'password']);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->mapAttributesRules([
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
            ],
            'password' => [
                'required',
                'string',
                Password::default(),
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            'data.attributes.email.required' => 'Email là trường bắt buộc.',
            'data.attributes.email.string' => 'Email nên là một chuỗi.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.max' => 'Email nên có độ dài tối đa 50.',
            'data.attributes.password.required' => 'Mật khẩu là trường bắt buộc.',
            'data.attributes.password.string' => 'Mật khẩu nên là một chuỗi.',
            'data.attributes.password.password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
        ];
    }
}
