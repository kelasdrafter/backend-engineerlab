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
        Schema::create('premium_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('premium_product_id');
            $table->string('reviewer_name');
            $table->string('reviewer_photo')->nullable();
            $table->text('review_text');
            $table->boolean('is_published')->default(true);
            $table->json('created_by')->nullable();
            $table->json('updated_by')->nullable();
            $table->json('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key
            $table->foreign('premium_product_id')
                  ->references('id')
                  ->on('premium_products')
                  ->onDelete('cascade');

            // Indexes
            $table->index('premium_product_id');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_product_reviews');
    }
};