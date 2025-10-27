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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->text('privilege')->nullable();
            $table->text('benefit')->nullable();
            $table->text('description');
            $table->decimal('price', 18, 2);
            $table->decimal('discount_price', 18, 2);
            $table->integer('total_minutes')->default(0);
            $table->unsignedBigInteger('category_id');
            $table->string('whatsapp_group_url')->nullable();
            $table->string('thumbnail_url');
            $table->string('syllabus_url');
            $table->string('trailer_url');

            $table->boolean('is_can_checkout')->default(true);
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
        Schema::dropIfExists('courses');
    }
};
