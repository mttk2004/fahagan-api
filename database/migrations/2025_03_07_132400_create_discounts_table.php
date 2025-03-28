<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary(); // Snowflake ID (64 bit)
            $table->string('name'); // Tên chương trình giảm giá
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent'); // Giảm giá theo %
            $table->decimal('discount_value', 10, 2); // Giá trị giảm giá (VD: 10% hoặc 50000 VND)
            $table->dateTime('start_date'); // Ngày bắt đầu
            $table->dateTime('end_date'); // Ngày kết thúc
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
