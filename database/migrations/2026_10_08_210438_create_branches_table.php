<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->string('street');
            $table->string('exterior_number', 50);
            $table->string('interior_number', 50)->nullable();
            $table->string('neighborhood');
            $table->string('postal_code', 10);
            $table->string('city');
            $table->string('state');
            $table->text('references')->nullable();

            $table->string('phone', 30)->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();            
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};