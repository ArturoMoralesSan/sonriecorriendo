<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('mercadopago_order_id')
                ->nullable()
                ->unique()
                ->after('folio');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique([
                'mercadopago_order_id',
            ]);

            $table->dropColumn('mercadopago_order_id');
        });
    }
};