<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 * @method static where(string $string, $address_id)
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable
        = [
            'user_id',
            'name',
            'phone',
            'city',
            'district',
            'ward',
            'address_line',
        ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
