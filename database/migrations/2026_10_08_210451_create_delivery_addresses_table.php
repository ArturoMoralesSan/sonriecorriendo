<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->unique()
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->string('delivery_method', 30);

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->nullOnDelete();

            // Copia histórica de la sucursal seleccionada.
            $table->string('branch_name')->nullable();
            $table->text('branch_address')->nullable();

            // Estos campos sólo se utilizan para entrega a domicilio.
            $table->string('street')->nullable();
            $table->string('exterior_number', 50)->nullable();
            $table->string('interior_number', 50)->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->text('references')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_addresses');
    }
};