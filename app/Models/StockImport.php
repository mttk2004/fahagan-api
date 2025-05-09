<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class StockImport extends Model
{
  use HasFactory;

  public $incrementing = false;  // Vô hiệu hóa tự động tăng ID

  protected $keyType = 'string'; // Kiểu khóa chính là string

  protected static function boot(): void
  {
    parent::boot();

    static::creating(function ($model) {
      $model->{$model->getKeyName()} = App::make('snowflake')->id();
    });
  }

  protected $fillable
  = [
    'employee_id',
    'supplier_id',
    'discount_value',
    'imported_at',
  ];

  protected function originalTotalCost(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->items->sum(fn(StockImportItem $item) => $item->sub_total),
    );
  }

  public function employee(): BelongsTo
  {
    return $this->belongsTo(User::class, 'employee_id');
  }

  public function supplier(): BelongsTo
  {
    return $this->belongsTo(Supplier::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(StockImportItem::class);
  }

  protected function casts(): array
  {
    return [
      'imported_at' => 'datetime',
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
    ];
  }
}
