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
        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_distance_id')
                ->constrained('race_distances')
                ->cascadeOnDelete();

            // Nombre de la categoría
            $table->string('name');

            // Descripción opcional
            $table->text('description')->nullable();

            // Edad mínima y máxima
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();

            // Género de la categoría
            $table->string('gender')->default('mixed');

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
                'sort_order',
            ]);

            $table->index([
                'race_distance_id',
                'gender',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_categories');
    }
};