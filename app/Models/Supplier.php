<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable
        = [
            'name',
            'phone',
            'email',
            'city',
            'district',
            'ward',
            'address_line',
        ];

    protected $dates = ['deleted_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function suppliedBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public function stockImports(): HasMany
    {
        return $this->hasMany(StockImport::class);
    }
}
