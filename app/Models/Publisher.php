<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($publisher_id)
 * @method static create(mixed $publisherData)
 */
class Publisher extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable
        = [
            'name',
            'biography',
        ];

    public function publishedBooks(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(DiscountTarget::class, 'target');
    }
}
