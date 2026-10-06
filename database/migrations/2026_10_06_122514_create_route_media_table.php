<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')
                ->constrained('routes')
                ->cascadeOnDelete();

            $table->enum('type', [
                'image',
                'video',
            ]);

            $table->string('file');

            $table->string('title')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_media');
    }
};
