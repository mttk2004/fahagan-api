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

        $genreRules = new GenreValidationRules();

        return [
            'data.attributes.name' => array_merge(
                ['sometimes'],
                array_filter(
                    $genreRules->getNameRuleWithUnique($genreId),
                    fn ($rule) => $rule !== 'required'
                )
            ),
            'data.attributes.slug' => array_merge(
                ['sometimes'],
                array_filter(
                    $genreRules->getSlugRuleWithUnique($genreId),
                    fn ($rule) => $rule !== 'required'
                )
            ),
            'data.attributes.description' => array_merge(
                ['sometimes'],
                array_filter(GenreValidationRules::DESCRIPTION->rules(), fn ($rule) => $rule !== 'required')
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => GenreValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => GenreValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => GenreValidationMessages::NAME_UNIQUE->message(),

            'data.attributes.slug.string' => GenreValidationMessages::SLUG_STRING->message(),
            'data.attributes.slug.max' => GenreValidationMessages::SLUG_MAX->message(),
            'data.attributes.slug.unique' => GenreValidationMessages::SLUG_UNIQUE->message(),

            'data.attributes.description.string' => GenreValidationMessages::DESCRIPTION_STRING->message(),
            'data.attributes.description.max' => GenreValidationMessages::DESCRIPTION_MAX->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_genres');
    }
}
