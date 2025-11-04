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
        Schema::create('master_ahsp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ahsp_source_id')->constrained('ahsp_sources')->restrictOnDelete()->comment('FK to ahsp_sources');
            $table->string('code', 50)->comment('Kode AHSP: 2.2.1.5.7');
            $table->text('name')->comment('Nama pekerjaan');
            $table->string('unit', 20)->comment('Satuan: m³, m², kg, dll');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['ahsp_source_id', 'code'], 'uk_source_code')->comment('Code unique per source');
            $table->index(['ahsp_source_id', 'is_active']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ahsp');
    }
};
