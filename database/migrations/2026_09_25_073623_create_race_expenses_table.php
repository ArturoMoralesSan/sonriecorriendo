
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
        Schema::create('race_expenses', function (Blueprint $table) {
            $table->id();

            // Carrera a la que pertenece el gasto
            $table->foreignId('race_id')
                ->constrained('races')
                ->cascadeOnDelete();

            // Usuario que registró el gasto
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Información del gasto
            $table->string('title');
            $table->string('category', 100)->nullable();
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();

            // Importe y fecha
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');

            // Control de pago
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 100)->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Comprobante y observaciones
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['race_id', 'status']);
            $table->index(['race_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_expenses');
    }
};
