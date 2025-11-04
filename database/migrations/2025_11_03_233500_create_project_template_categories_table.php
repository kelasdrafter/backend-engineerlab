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
        Schema::create('project_template_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('project_templates')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('project_template_categories')->cascadeOnDelete()->comment('Untuk nested category');
            $table->string('name', 255);
            $table->string('code', 50)->nullable()->comment('Kode kategori');
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['template_id', 'parent_id']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_template_categories');
    }
};
