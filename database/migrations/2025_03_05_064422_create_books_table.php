<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->unsignedSmallInteger('publisher_id');
            $table->foreign('publisher_id')
                  ->references('id')
                  ->on('publishers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->string('title');
            $table->text('description');
            $table->decimal('price', 9, 1)->unsigned();
            $table->unsignedSmallInteger('edition')->default(1);
            $table->unsignedSmallInteger('pages');
            $table->date('publication_date');
            $table->string('image_url')->default('');
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['title', 'edition']);
            $table->index(['title', 'edition', 'sold_count', 'publication_date'], 'books_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
