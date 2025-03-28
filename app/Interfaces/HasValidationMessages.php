<?php

namespace App\Interfaces;

interface HasValidationMessages
{
    public function messages(): array;

    public function rules(): array;
}
