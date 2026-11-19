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
        Schema::table('race_prices', function (Blueprint $table) {
            // Enlace de inscripción para esta etapa/precio
            $table->string('registration_url', 2048)
                ->nullable()
                ->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_prices', function (Blueprint $table) {
            $table->dropColumn('registration_url');
        });
    }
};
