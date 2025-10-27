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
        Schema::create('free_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('thumbnail_url');
            $table->string('assets_url');
            $table->string('tags');
            $table->text('description');

            $table->json('updated_by')->nullable();
            $table->json('created_by')->nullable();
            $table->json('deleted_by')->nullable();
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_resources');
    }
};
