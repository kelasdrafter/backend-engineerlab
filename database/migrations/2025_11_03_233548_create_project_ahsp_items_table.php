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
        Schema::create('project_ahsp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_ahsp_id')->constrained('project_ahsp')->cascadeOnDelete();
            $table->enum('category', ['material', 'labor', 'equipment']);
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('coefficient', 10, 4);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['project_ahsp_id', 'category']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_ahsp_items');
    }
};
