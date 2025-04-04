<?php

namespace App\Http\Requests\V1;

use App\Enums\User\UserValidationMessages;
use App\Enums\User\UserValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class UserUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // User không có relationships, được phép sử dụng direct format
        $this->convertToJsonApiFormat([
            'first_name',
            'last_name',
            'phone',
            'email'
        ]);
    }

    public function rules(): array
    {
        // Lấy ID người dùng từ route parameter
        $userId = request()->route('user');

        $attributesRules = $this->mapAttributesRules([
            'first_name' => HasUpdateRules::transformToUpdateRules(UserValidationRules::FIRST_NAME->rules()),
            'last_name' => HasUpdateRules::transformToUpdateRules(UserValidationRules::LAST_NAME->rules()),
            'phone' => HasUpdateRules::transformToUpdateRules(UserValidationRules::getPhoneRuleWithUnique($userId)),
            'email' => HasUpdateRules::transformToUpdateRules(UserValidationRules::getEmailRuleWithUnique($userId)),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return UserValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::user() && AuthUtils::user()->id == request()->route('user');
    }
}
