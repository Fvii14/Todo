<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajusta el "after()" si tu tabla no tiene brevo_id
            $table->string('holded_contact_id', 64)->nullable()->after('brevo_id');
            // Recomendado: único (en MySQL permite múltiples NULL)
            $table->unique('holded_contact_id', 'users_holded_contact_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_holded_contact_id_unique');
            $table->dropColumn('holded_contact_id');
        });
    }
};
