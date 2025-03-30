<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
	public function up(): void
	{
		Schema::create('orders', function(Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users');
			$table->string('status');
			$table->decimal('total_amount');
			$table->string('shopping_name');
			$table->string('shopping_phone');
			$table->string('shopping_city');
			$table->string('shopping_ward');
			$table->string('shopping_address_line');
			$table->timestamp('ordered_at');
			$table->timestamp('approved_at')->nullable();
			$table->timestamp('canceled_at')->nullable();
			$table->timestamp('delivered_at')->nullable();
			$table->timestamp('returned_at')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
