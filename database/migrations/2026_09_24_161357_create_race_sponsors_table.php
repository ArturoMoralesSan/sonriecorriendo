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
        Schema::create('race_sponsors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            $table->foreignId('sponsor_id')
                ->constrained('sponsors')
                ->cascadeOnDelete();

            $table->string('type')
                ->default('sponsor');

            $table->decimal('amount', 10, 2)
                ->nullable();

            $table->text('benefits')
                ->nullable();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'race_id',
                'is_active',
            ]);

            $table->index([
                'race_id',
                'sort_order',
            ]);

            $table->index([
                'sponsor_id',
                'is_active',
            ]);

            $table->unique([
                'race_id',
                'sponsor_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_sponsors');
    }
};
