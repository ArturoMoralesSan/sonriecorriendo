<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Precio final (MXN) de inscripción con descuento para participantes
     * pasados (costo total, no la rebaja); se muestra en la página de la carrera.
     */
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)
                ->nullable()
                ->after('discount_registration_url');
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
