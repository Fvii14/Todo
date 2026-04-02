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
        Schema::dropIfExists('metricas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('metricas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id', 64)->nullable()->index();
            $table->string('name');
            $table->json('metadata')->nullable();
            $table->index(['name', 'user_id']);
            $table->index(['session_id', 'name']);
            $table->timestamps();
        });
    }
};
