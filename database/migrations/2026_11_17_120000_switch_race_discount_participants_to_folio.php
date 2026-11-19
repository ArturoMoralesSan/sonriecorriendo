<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los descuentos ahora se validan por número de folio + nombres
     * + apellidos (en lugar de CURP). Las CURPs importadas no son
     * compatibles, así que se vacía la lista.
     */
    public function up(): void
    {
        DB::table('race_discount_participants')->delete();

        // Primero el índice nuevo: en MySQL la llave foránea de race_id
        // necesita un índice que empiece por race_id.
        Schema::table('race_discount_participants', function (Blueprint $table) {
            $table->string('folio', 50)->after('race_id');
            $table->string('first_name')->after('folio');
            $table->string('last_name')->after('first_name');

            // Nombre completo normalizado (sin acentos, mayúsculas)
            // para buscar solo por nombre.
            $table->string('name_key')->after('last_name');

            $table->unique(['race_id', 'folio']);
            $table->index(['race_id', 'name_key']);
        });

        Schema::table('race_discount_participants', function (Blueprint $table) {
            $table->dropUnique(['race_id', 'curp']);
            $table->dropColumn('curp');
        });
    }

    public function down(): void
    {
        DB::table('race_discount_participants')->delete();

        Schema::table('race_discount_participants', function (Blueprint $table) {
            $table->string('curp', 18)->after('race_id');
            $table->unique(['race_id', 'curp']);
        });

        Schema::table('race_discount_participants', function (Blueprint $table) {
            $table->dropIndex(['race_id', 'name_key']);
            $table->dropUnique(['race_id', 'folio']);
            $table->dropColumn(['folio', 'first_name', 'last_name', 'name_key']);
        });
    }
};
