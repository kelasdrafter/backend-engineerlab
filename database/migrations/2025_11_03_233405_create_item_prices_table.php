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
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->decimal('price', 15, 2)->comment('Harga satuan');
            $table->date('effective_date')->comment('Berlaku mulai');
            $table->date('expired_date')->nullable()->comment('Berlaku sampai');
            $table->boolean('is_active')->default(true);
            $table->string('source', 100)->nullable()->comment('Sumber harga: Perda, Survey, dll');
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['item_id', 'region_id', 'effective_date'], 'uk_item_region_date');
            $table->index(['region_id', 'is_active']);
            $table->index(['effective_date', 'expired_date']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};
