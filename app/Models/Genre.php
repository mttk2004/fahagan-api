<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail($genre_id)
 * @method static create(mixed $genreData)
 */
class Genre extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable
        = [
            'name',
            'slug',
            'description',
        ];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_genre');
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(DiscountTarget::class, 'target');
    }
}
