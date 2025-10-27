<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('insight_categories')->onDelete('restrict');
            $table->string('title', 500);
            $table->string('slug', 500)->unique();
            $table->longText('content');
            $table->integer('view_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};