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
        Schema::create('checklist_template_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('checklist_template_id')
                ->constrained('checklist_templates')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('category')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->index(['checklist_template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_template_items');
    }
};
