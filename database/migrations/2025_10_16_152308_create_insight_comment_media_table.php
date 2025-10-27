<?php
// database/migrations/2025_10_16_create_insight_comment_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_comment_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('insight_comments')->onDelete('cascade');
            $table->enum('type', ['video', 'image', 'file']);
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('comment_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_comment_media');
    }
};