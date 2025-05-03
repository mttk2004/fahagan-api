<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('payments', function (Blueprint $table) {
      $table->unsignedBigInteger('id')->primary();

      $table->unsignedBigInteger('order_id');
      $table->foreign('order_id')
        ->references('id')
        ->on('orders')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->enum('status', [
        'pending',
        'paid',
        'failed',
      ])->default('pending');
      $table->enum('method', [
        'cod',
        'bank_transfer',
        'credit_card',
        'vnpay',
        'paypal',
      ])->default('cod');
      $table->decimal('total_amount', 12, 1);
      $table->string('transaction_ref')->nullable();
      $table->json('gateway_response')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('payments');
  }
};
