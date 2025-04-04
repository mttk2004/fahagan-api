<?php

namespace App\Http\Requests\V1;

use App\Enums\Author\AuthorValidationMessages;
use App\Enums\Author\AuthorValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::NAME->rules()),
            'biography' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::BIOGRAPHY->rules()),
            'image_url' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::IMAGE_URL->rules()),
        ]);

        $relationshipsRules = [
            'data.relationships.books.data.*.id' => HasUpdateRules::transformToUpdateRules(
                AuthorValidationRules::BOOK_ID->rules()
            ),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return AuthorValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }
}
