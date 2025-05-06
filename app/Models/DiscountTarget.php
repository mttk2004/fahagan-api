<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @method static insert(array $records)
 * @method static where(string $string, $id)
 */
class DiscountTarget extends Model
{
    public $timestamps = false;

    protected $fillable
        = [
            'discount_id',
            'target_id',
        ];

    /**
     * The primary key for the model.
     *
     * @var array
     */
    protected $primaryKey = ['discount_id', 'target_id'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Lấy sách được áp dụng giảm giá
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'target_id');
    }
}
