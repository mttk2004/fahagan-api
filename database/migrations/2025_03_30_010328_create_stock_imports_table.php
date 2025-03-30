<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_imports', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedSmallInteger('supplier_id');
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->decimal('total_cost', 15, 1)->unsigned();
            $table->timestamp('imported_at')->default(now());
            $table->timestamps();
        });

        Schema::create('stock_import_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')
                  ->autoIncrement()
                  ->startingValue(10000)
                  ->primary();

            $table->unsignedBigInteger('stock_import_id');
            $table->foreign('stock_import_id')
                  ->references('id')
                  ->on('stock_imports')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
                  ->references('id')
                  ->on('books')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 9, 1)->unsigned();

            $table->unique(['stock_import_id', 'book_id']);
            $table->index(['stock_import_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_import_items');
        Schema::dropIfExists('stock_imports');
    }
};
