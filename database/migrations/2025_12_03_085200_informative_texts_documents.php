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
        Schema::table('documents', function (Blueprint $table) {
            $table->text('informative_clickable_text')->nullable()->after('allowed_types');
            $table->text('informative_header_text')->nullable()->after('informative_clickable_text');
            $table->text('informative_link')->nullable()->after('informative_header_text');
            $table->text('informative_link_text')->nullable()->after('informative_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('informative_clickable_text');
            $table->dropColumn('informative_header_text');
            $table->dropColumn('informative_link');
            $table->dropColumn('informative_link_text');
        });
    }
};
