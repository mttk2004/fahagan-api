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
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->string('phone', 10)->unique();
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->boolean('is_customer')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_name', 'last_name', 'phone', 'email'], 'users_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
