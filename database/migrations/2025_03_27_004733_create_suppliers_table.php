<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
	public function up(): void
	{
		Schema::create('suppliers', function(Blueprint $table) {
			$table->unsignedSmallInteger('id')
				  ->autoIncrement()
				  ->startingValue(10000)
				  ->primary();
			$table->string('name')->unique();
			$table->string('phone')->unique();
			$table->string('email')->unique();
			$table->string('city');
			$table->string('ward');
			$table->string('address_line');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('suppliers');
	}
};
