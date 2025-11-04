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
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('ahsp_source_id')->constrained('ahsp_sources')->restrictOnDelete()->comment('Template standard AHSP');
            $table->boolean('is_global')->default(false)->comment('TRUE=Admin template, FALSE=User private');
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_global', 'is_active']);
            $table->index('created_by');
            $table->index('ahsp_source_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};
