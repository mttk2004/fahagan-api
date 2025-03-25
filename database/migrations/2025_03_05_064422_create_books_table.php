<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
	public function up(): void
	{
		Schema::create('books', function(Blueprint $table) {
			$table->bigInteger('id')->unsigned()->primary(); // Snowflake ID (64 bit)
			$table->string('title');
			$table->text('description');
			$table->decimal('price', 9, 1)->unsigned();
			$table->unsignedSmallInteger('edition')->default(1);
			$table->unsignedSmallInteger('pages');
			$table->date('publication_date');
			$table->string('image_url')->default('');
			$table->unsignedSmallInteger('available_count')->default(0);
			$table->unsignedInteger('sold_count')->default(0);
			$table->timestamps();
			$table->softDeletes();

			// Foreign key: publisher_id, unsigned small integer
			$table->unsignedSmallInteger('publisher_id');
			$table->foreign('publisher_id')
				  ->references('id')
				  ->on('publishers')
				  ->onDelete('cascade')
				  ->onUpdate('cascade');

			// Unique constraint on title and edition
            $table->unique(['title', 'edition']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('books');
	}
};
