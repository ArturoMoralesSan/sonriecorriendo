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
        Schema::create('race_galleries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            $table->string('image');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'race_id',
                'sort_order',
            ]);

            $table->index([
                'race_id',
                'is_active',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_galleries');
    }
};
