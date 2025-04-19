<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('discount_targets', function (Blueprint $table) {
      $table->unsignedBigInteger('discount_id');
      $table->foreign('discount_id')
        ->references('id')
        ->on('discounts')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      // Thay đổi từ morphs thành trực tiếp liên kết với book hoặc order
      $table->unsignedBigInteger('target_id');

      // Thêm index
      $table->index(['discount_id', 'target_id']);

      $table->primary(['discount_id', 'target_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('discount_targets');
  }
};
