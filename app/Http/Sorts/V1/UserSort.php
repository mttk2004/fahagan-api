<?php

namespace App\Http\Sorts\V1;

class UserSort extends Sort
{
    protected array $sortableColumns
        = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'is_customer',
            'last_login',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
}
