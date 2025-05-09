<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlePagination
{
    protected function getPerPage(Request $request, int $default = 20, int $maxPerPage = 10000): int
    {
        $perPage = (int) $request->get('per_page', $default);

        if ($perPage < 10) {
            return $default;
        }

        return min($perPage, $maxPerPage);
    }
}
