<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_ranks', function (Blueprint $table) {  // ← BENAR: insight_ranks
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->integer('min_points');
            $table->integer('max_points')->nullable();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('min_points');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_ranks');  // ← BENAR: insight_ranks
    }
};