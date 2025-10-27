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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('allow_access')->nullable();
            $table->string('client_id')->nullable();
            $table->string('password')->nullable();
            $table->string('uuid_client')->nullable();
            $table->string('motherboard_client')->nullable();
            $table->string('processor_client')->nullable();
            $table->integer('client_login')->nullable();
            $table->integer('client_logout')->nullable();

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
        Schema::dropIfExists('licenses');
    }
};
