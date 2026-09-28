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
        Schema::create('races', function (Blueprint $table) {
            $table->id();

            // Información general
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Fecha y horario del evento
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Ubicación
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('México');

            // Material gráfico
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();

            // Inscripciones
            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();

            // Estado
            $table->string('status')->default('draft')->index();

            // Información adicional
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};