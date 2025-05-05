<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->unsignedBigInteger('employee_id')->nullable();
            // Nullable because the order only has a customer when it is created
            // and the employee is assigned later.
            $table->foreign('employee_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->enum('status', [
              'pending',
              'approved',
              'delivered',
              'completed',
              'canceled',
            ])->default('pending');

            $table->string('shopping_name');
            $table->string('shopping_phone');
            $table->string('shopping_city');
            $table->string('shopping_district');
            $table->string('shopping_ward');
            $table->string('shopping_address_line');

            $table->timestamp('ordered_at')->default(now());
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'employee_id', 'status', 'ordered_at'], 'orders_index');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')
              ->autoIncrement()
              ->startingValue(10000)
              ->primary();

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')
              ->references('id')
              ->on('orders')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')
              ->references('id')
              ->on('books')
              ->onDelete('cascade')
              ->onUpdate('cascade');

            $table->unsignedSmallInteger('quantity');
            $table->decimal('price_at_time', 9, 1)->unsigned();
            $table->decimal('discount_value', 9, 1)->unsigned()->default(0.0);

            $table->unique(['order_id', 'book_id']);
            $table->index(['order_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
