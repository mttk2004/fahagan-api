<?php

namespace App\Http\Sorts\V1;


class OrderSort extends Sort
{
    protected array $sortableColumns
        = [
            'id',
            'user_id',
            'status',
            'total_amount',
            'ordered_at',
            'approved_at',
            'delivered_at',
            'canceled_at',
            'created_at',
            'updated_at',
        ];
}
