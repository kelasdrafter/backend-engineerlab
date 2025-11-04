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
        Schema::create('project_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_category_id')->constrained('project_template_categories')->cascadeOnDelete();
            $table->enum('item_type', ['ahsp', 'custom'])->default('ahsp');
            $table->foreignId('master_ahsp_id')->nullable()->constrained('master_ahsp')->nullOnDelete()->comment('If item_type = ahsp');
            $table->string('code', 50)->nullable()->comment('If item_type = custom');
            $table->text('name')->nullable()->comment('If item_type = custom');
            $table->string('unit', 20)->nullable()->comment('If item_type = custom');
            $table->decimal('default_volume', 15, 4)->default(0)->comment('Default volume');
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('template_category_id');
            $table->index('item_type');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_template_items');
    }
};
