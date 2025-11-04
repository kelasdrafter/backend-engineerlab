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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('project_templates')->nullOnDelete()->comment('Jika dari template');
            $table->foreignId('ahsp_source_id')->constrained('ahsp_sources')->restrictOnDelete()->comment('Source AHSP yang digunakan');
            
            $table->decimal('overhead_percentage', 5, 2)->default(10.00)->comment('Overhead %');
            $table->decimal('profit_percentage', 5, 2)->default(10.00)->comment('Keuntungan %');
            $table->decimal('ppn_percentage', 5, 2)->default(11.00)->comment('PPN %');
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->comment('Owner project');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['created_by', 'is_active']);
            $table->index('region_id');
            $table->index('ahsp_source_id');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
