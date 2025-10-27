<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learn_corners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique()->index();
            $table->string('title');
            $table->text('description');
            $table->string('video_url'); // YouTube URL
            $table->string('thumbnail_url')->nullable(); // Null = use YouTube thumbnail
            $table->string('level'); // Admin ketik manual (Pemula, Menengah, Lanjutan, dll)
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->json('updated_by')->nullable();
            $table->json('created_by')->nullable();
            $table->json('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learn_corners');
    }
};