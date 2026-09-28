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
        Schema::create('race_inclusions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_distance_id')
                ->constrained('race_distances')
                ->cascadeOnDelete();

            // Nombre del beneficio o artículo incluido
            $table->string('name');

            // Descripción opcional
            $table->text('description')->nullable();

            // Tipo de inclusión
            $table->string('type')->default('benefit');

            // Permite desactivar una inclusión sin eliminarla
            $table->boolean('included')->default(true);

            // Orden en que se mostrará
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'race_distance_id',
                'included',
            ]);

            $table->index([
                'race_distance_id',
                'sort_order',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_inclusions');
    }
};