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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Kode item');
            $table->text('name')->comment('Nama item');
            $table->enum('type', ['material', 'labor', 'equipment'])->comment('Material, Upah, atau Alat');
            $table->string('unit', 20)->comment('Satuan: kg, m³, OH, jam, dll');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['type', 'is_active']);
            $table->index('code');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
