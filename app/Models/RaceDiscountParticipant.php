<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RaceDiscountParticipant extends Model
{
    protected $fillable = [
        'race_id',
        'folio',
        'first_name',
        'last_name',
        'name_key',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $participant) {
            $participant->name_key = self::fullNameKey(
                $participant->first_name,
                $participant->last_name,
            );
        });
    }

    /**
     * Clave de búsqueda del nombre completo: "Nombres Apellidos"
     * normalizado.
     */
    public static function fullNameKey(?string $firstName, ?string $lastName): string
    {
        return self::normalizeName(trim("{$firstName} {$lastName}"));
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /**
     * Normaliza el folio: solo quita espacios a los lados.
     * El folio se compara de forma exacta.
     */
    public static function normalizeFolio(?string $folio): string
    {
        return trim((string) $folio);
    }

    /**
     * Normaliza un nombre para compararlo: sin acentos, sin signos,
     * en mayúsculas y con espacios simples.
     * "  Martínez  Luna " → "MARTINEZ LUNA"
     */
    public static function normalizeName(?string $name): string
    {
        $name = Str::upper(Str::ascii((string) $name));
        $name = preg_replace('/[^A-Z0-9 ]+/', ' ', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
