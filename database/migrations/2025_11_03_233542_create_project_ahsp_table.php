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
        Schema::create('project_ahsp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('ahsp_source_id')->constrained('ahsp_sources')->restrictOnDelete()->comment('Must match project source');
            $table->enum('source_type', ['master', 'custom'])->default('master');
            $table->foreignId('master_ahsp_id')->nullable()->constrained('master_ahsp')->nullOnDelete()->comment('If from master');
            
            $table->string('code', 50);
            $table->text('name');
            $table->string('unit', 20);
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['project_id', 'source_type']);
            $table->index('ahsp_source_id');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_ahsp');
    }
};
