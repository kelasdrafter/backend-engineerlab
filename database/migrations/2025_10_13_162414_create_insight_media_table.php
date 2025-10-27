<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insight_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insight_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['video', 'image', 'file']);
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('insight_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_media');
    }
};