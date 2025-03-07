<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


class Book extends Model
{
	use HasFactory, SoftDeletes;

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
			'publisher_id',
			'title',
			'description',
			'price',
			'edition',
			'pages',
			'publication_date',
			'available_count',
			'sold_count',
		];

	protected function casts(): array
	{
		return [
			'publication_date' => 'date',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
			'deleted_at' => 'datetime',
		];
	}

	public function authors(): BelongsToMany
	{
		return $this->belongsToMany(Author::class, 'author_book');
	}

	public function publisher(): BelongsTo
	{
		return $this->belongsTo(Publisher::class);
	}

	public function genres(): BelongsToMany
	{
		return $this->belongsToMany(Genre::class, 'book_genre');
	}
}
