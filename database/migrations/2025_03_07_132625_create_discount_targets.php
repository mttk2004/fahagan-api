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
            $table->morphs('target'); // Tạo cột `target_id` và `target_type` (Polymorphic)

            $table->primary(['discount_id', 'target_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_targets');
    }
};
