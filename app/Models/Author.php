<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(mixed $authorData)
 * @method static findOrFail($author_id)
 */
class Author extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable
        = [
          'name',
          'biography',
          'image_url',
        ];

    public function writtenBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'author_book');
    }
}
