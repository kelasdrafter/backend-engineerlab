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
        Schema::create('ahsp_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('CK, BM, SDA, KE, CUSTOM');
            $table->string('name', 100)->comment('Cipta Karya, Bina Marga, etc');
            $table->text('description')->nullable()->comment('Untuk jenis pekerjaan apa');
            $table->string('icon', 50)->nullable()->comment('Emoji icon untuk UI');
            $table->string('color', 20)->nullable()->comment('HEX color code untuk badge');
            $table->boolean('is_active')->default(true)->comment('Status aktif');
            $table->integer('sort_order')->default(0)->comment('Urutan tampilan');
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahsp_sources');
    }
};
