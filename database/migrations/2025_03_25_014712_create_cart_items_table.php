<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()
              ->startingValue(10000)->primary();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
              ->references('id')
              ->on('books')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->unsignedSmallInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
