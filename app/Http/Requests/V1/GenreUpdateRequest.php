<?php

namespace App\Http\Requests\V1;

use App\Enums\Genre\GenreValidationMessages;
use App\Enums\Genre\GenreValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rule;

class GenreUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        // Lấy ID thể loại từ route parameter
        $genreId = request()->route('genre');

        return [
            'name' => array_merge(
                ['sometimes'],
                array_filter(
                    GenreValidationRules::NAME->getNameRuleWithUnique($genreId),
                    fn ($rule) => $rule !== 'required'
                )
            ),
            'slug' => array_merge(
                ['sometimes'],
                array_filter(
                    GenreValidationRules::SLUG->getSlugRuleWithUnique($genreId),
                    fn ($rule) => $rule !== 'required'
                )
            ),
            'description' => array_merge(
                ['sometimes'],
                array_filter(GenreValidationRules::DESCRIPTION->rules(), fn ($rule) => $rule !== 'required')
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => GenreValidationMessages::NAME_STRING->message(),
            'name.max' => GenreValidationMessages::NAME_MAX->message(),
            'name.unique' => GenreValidationMessages::NAME_UNIQUE->message(),

            'slug.string' => GenreValidationMessages::SLUG_STRING->message(),
            'slug.max' => GenreValidationMessages::SLUG_MAX->message(),
            'slug.unique' => GenreValidationMessages::SLUG_UNIQUE->message(),

            'description.string' => GenreValidationMessages::DESCRIPTION_STRING->message(),
            'description.max' => GenreValidationMessages::DESCRIPTION_MAX->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_genres');
    }
}
