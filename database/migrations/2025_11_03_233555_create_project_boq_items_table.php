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
        Schema::create('project_boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->constrained('project_categories')->cascadeOnDelete();
            $table->enum('item_type', ['ahsp', 'custom'])->default('ahsp');
            $table->foreignId('project_ahsp_id')->nullable()->constrained('project_ahsp')->nullOnDelete()->comment('If item_type = ahsp');
            
            $table->string('code', 50);
            $table->text('name');
            $table->string('unit', 20);
            $table->decimal('volume', 15, 4)->default(0);
            
            $table->decimal('unit_price', 15, 2)->default(0)->comment('Harga satuan calculated');
            $table->decimal('total_price', 15, 2)->default(0)->comment('volume * unit_price');
            
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['project_category_id', 'item_type']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_boq_items');
    }
};
