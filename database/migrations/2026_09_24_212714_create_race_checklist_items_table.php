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
        Schema::create('race_checklist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            $table->foreignId('checklist_template_item_id')
                ->nullable()
                ->constrained('checklist_template_items')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('category')->nullable();

            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'not_applicable',
            ])->default('pending');

            $table->date('due_date')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['race_id', 'status']);
            $table->index(['race_id', 'category']);
            $table->index(['race_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_checklist_items');
    }
};
