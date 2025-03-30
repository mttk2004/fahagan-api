<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                  ->references('id')
                  ->on('books')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedBigInteger('stock_import_item_id');
            $table->foreign('stock_import_item_id')
                  ->references('id')
                  ->on('stock_import_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->foreign('order_item_id')
                  ->references('id')
                  ->on('order_items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->enum('status', ['available', 'sold', 'returned'])
                  ->default('available');
            $table->timestamp('imported_at')->default(now());
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_instances');
    }
};
