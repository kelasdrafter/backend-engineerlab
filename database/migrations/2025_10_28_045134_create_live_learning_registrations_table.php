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
        Schema::create('live_learning_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_learning_id');
            $table->string('name');
            $table->string('email');
            $table->string('whatsapp', 20);
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('live_learning_id')
                  ->references('id')
                  ->on('live_learnings')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('live_learning_id');
            $table->index('email');
            $table->index('registered_at');
            
            // Unique constraint: 1 email hanya bisa daftar 1x per live learning
            $table->unique(['live_learning_id', 'email'], 'unique_registration_per_live_learning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_learning_registrations');
    }
};