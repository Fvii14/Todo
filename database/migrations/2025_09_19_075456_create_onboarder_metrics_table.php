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
        Schema::create('onboarder_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarder_id')->constrained('onboarders')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('onboarder_sections')->onDelete('cascade');
            $table->foreignId('conviviente_type_id')->nullable()->constrained('conviviente_types')->onDelete('cascade');
            $table->string('action');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['onboarder_id', 'action']);
            $table->index(['section_id', 'action']);
            $table->index(['conviviente_type_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarder_metrics');
    }
};
