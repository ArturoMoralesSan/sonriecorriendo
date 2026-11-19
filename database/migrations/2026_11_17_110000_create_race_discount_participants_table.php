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
        // Enlace de registro con descuento para participantes pasados
        Schema::table('races', function (Blueprint $table) {
            $table->string('discount_registration_url', 2048)
                ->nullable()
                ->after('results_url');
        });

        // CURPs de participantes pasados (importadas desde Excel)
        Schema::create('race_discount_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            $table->string('curp', 18);

            $table->timestamps();

            $table->unique(['race_id', 'curp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_discount_participants');

        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('discount_registration_url');
        });
    }
};
