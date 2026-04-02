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
        Schema::create('ayuda_requisitos_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayuda_id')->constrained('ayudas')->onDelete('cascade');
            $table->integer('version_number');
            $table->json('json_regla');
            $table->text('descripcion')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_draft')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->text('version_description')->nullable();
            $table->timestamps();

            $table->index(['ayuda_id', 'is_active'], 'arv_aid_active_idx');
            $table->index(['ayuda_id', 'is_draft'], 'arv_aid_draft_idx');
            $table->unique(['ayuda_id', 'version_number'], 'arv_aid_version_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayuda_requisitos_versions');
    }
};
