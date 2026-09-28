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
        Schema::create('race_distances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            // Información de la distancia
            $table->string('name');
            $table->decimal('distance', 6, 2);
            $table->string('unit')->default('km');

            // Horario específico de esta distancia
            $table->time('start_time')->nullable();

            // Capacidad máxima de participantes
            $table->unsignedInteger('capacity')->nullable();

            // Orden de presentación
            $table->unsignedInteger('sort_order')->default(0);

            // Estado
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['race_id', 'is_active']);
            $table->index(['race_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_distances');
    }
};