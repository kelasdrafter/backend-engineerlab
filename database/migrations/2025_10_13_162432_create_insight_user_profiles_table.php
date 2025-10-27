<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('insight_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->foreignId('current_rank_id')->nullable()->constrained('insight_ranks')->onDelete('set null');
            $table->timestamps();

            $table->index('user_id');
            $table->index('total_points');
            $table->index('current_rank_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_user_profiles');
    }
};