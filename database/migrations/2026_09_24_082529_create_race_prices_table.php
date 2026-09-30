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
        Schema::create('race_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_distance_id')
                ->constrained('race_distances')
                ->cascadeOnDelete();

            // Nombre de la etapa
            $table->string('name');

            // Precio de inscripción
            $table->decimal('price', 10, 2);

            // Vigencia del precio
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();

            // Cantidad máxima de inscripciones con este precio
            $table->unsignedInteger('capacity')->nullable();

            // Orden de presentación
            $table->unsignedInteger('sort_order')->default(0);

            // Estado
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'race_distance_id',
                'is_active',
            ]);

            $table->index([
                'race_distance_id',
                'starts_at',
                'ends_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_prices');
    }
};
