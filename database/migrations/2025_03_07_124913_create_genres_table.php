<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')
                ->autoIncrement()
                ->startingValue(10000)
                ->primary();
            $table->string('name')->unique()->index();
            $table->text('description');
        });

        Schema::create('book_genre', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedSmallInteger('genre_id');
            $table->foreign('genre_id')
                ->references('id')
                ->on('genres')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->primary(['genre_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_genre');
        Schema::dropIfExists('genres');
    }
};
