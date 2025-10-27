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
        Schema::create('premium_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('premium_product_id');
            $table->string('voucher_code')->nullable();
            $table->string('status', 50);
            $table->json('meta')->nullable();
            $table->decimal('amount', 18, 2);
            $table->string('snap_id')->nullable();
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
            $table->index('voucher_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_transactions');
    }
};