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
        Schema::create('project_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('price', 15, 2);
            $table->enum('source_type', ['system', 'manual'])->default('system')->comment('From system or user input');
            $table->string('source_reference', 255)->nullable()->comment('Perda, Survey date, etc');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['project_id', 'item_id'], 'uk_project_item');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_item_prices');
    }
};
