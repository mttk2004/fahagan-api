<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
