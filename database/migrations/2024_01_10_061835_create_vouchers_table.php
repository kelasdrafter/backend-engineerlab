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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->enum('type', ['Persentase', 'Fixed']);
            $table->decimal('nominal', 18, 2);
            $table->string('name');
            $table->integer('quota');
            $table->text('description');
            $table->string('thumbnail_url');
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();

            $table->boolean('is_public')->default(false);
            $table->boolean('is_repeatable')->default(false);
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
        Schema::dropIfExists('vouchers');
    }
};
