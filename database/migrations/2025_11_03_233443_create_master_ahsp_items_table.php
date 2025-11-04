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
        Schema::create('master_ahsp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_ahsp_id')->constrained('master_ahsp')->cascadeOnDelete();
            $table->enum('category', ['material', 'labor', 'equipment'])->comment('Material, Upah, atau Alat');
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete()->comment('FK to items');
            $table->decimal('coefficient', 10, 4)->comment('Koefisien penggunaan');
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['master_ahsp_id', 'category']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_ahsp_items');
    }
};
