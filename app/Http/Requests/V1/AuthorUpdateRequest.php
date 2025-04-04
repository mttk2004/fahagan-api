<?php

namespace App\Http\Requests\V1;

use App\Enums\Author\AuthorValidationMessages;
use App\Enums\Author\AuthorValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Models\Author;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class AuthorUpdateRequest extends BaseRelationshipRequest
{
    use HasUpdateRules;

    /**
     * Lấy danh sách các attribute cần chuyển đổi
     */
    protected function getAttributeNames(): array
    {
        return [
            'name',
            'biography',
            'image_url'
        ];
    }

    /**
     * Lấy quy tắc cho attributes
     */
    protected function getAttributeRules(): array
    {
        $id = request()->route('author');

        return [
            'name' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::NAME->rules()),
            'biography' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::BIOGRAPHY->rules()),
            'image_url' => HasUpdateRules::transformToUpdateRules(AuthorValidationRules::IMAGE_URL->rules()),
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.books.data.*.id' => HasUpdateRules::transformToUpdateRules(
                AuthorValidationRules::BOOK_ID->rules()
            ),
        ];
    }

    /**
     * Lấy lớp ValidationMessages
     */
    protected function getValidationMessagesClass(): string
    {
        return AuthorValidationMessages::class;
    }

    /**
     * Kiểm tra authorization
     */
    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }
}
