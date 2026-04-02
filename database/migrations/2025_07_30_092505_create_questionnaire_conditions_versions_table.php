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
        Schema::create('questionnaire_conditions_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->onDelete('cascade');
            $table->integer('version_number');
            $table->json('conditions_data');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_draft')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->text('version_description')->nullable();
            $table->timestamps();

            $table->index(['questionnaire_id', 'is_active'], 'qcv_qid_active_idx');
            $table->index(['questionnaire_id', 'is_draft'], 'qcv_qid_draft_idx');
            $table->unique(['questionnaire_id', 'version_number'], 'qcv_qid_version_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_conditions_versions');
    }
};
