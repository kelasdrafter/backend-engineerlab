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
        Schema::create('premium_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('premium_product_id');
            $table->string('premium_transaction_id');
            $table->string('status', 50);
            $table->json('created_by')->nullable();
            $table->json('updated_by')->nullable();
            $table->json('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('premium_product_id')
                  ->references('id')
                  ->on('premium_products')
                  ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('premium_product_id');
            $table->index('status');
            $table->index('premium_transaction_id');

            // Unique Constraint
            $table->unique(['user_id', 'premium_product_id'], 'unique_user_product_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_purchases');
    }
};