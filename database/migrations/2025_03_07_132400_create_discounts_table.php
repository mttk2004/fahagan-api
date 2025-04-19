<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('discounts', function (Blueprint $table) {
      $table->unsignedBigInteger('id')->primary();
      $table->string('name');
      $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
      $table->decimal('discount_value', 9, 1)->unsigned();
      $table->enum('target_type', ['book', 'order'])->default('book');
      $table->dateTime('start_date');
      $table->dateTime('end_date');
      $table->text('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->softDeletes();

      $table->unique('name', 'discounts_name_unique');
      $table->index(['name', 'discount_type', 'discount_value', 'target_type', 'start_date', 'end_date'], 'discounts_index');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('discounts');
  }
};
