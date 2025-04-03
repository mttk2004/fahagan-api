<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')
                  ->autoIncrement()
                  ->startingValue(10000)
                  ->primary();
            $table->string('name')->unique()->index();
            $table->text('biography');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publishers');
    }
};
