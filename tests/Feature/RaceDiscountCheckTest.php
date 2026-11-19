<?php

namespace Tests\Feature;

use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RaceDiscountCheckTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://registro.test/descuento';

    private function makeRace(?string $url = self::URL): Race
    {
        $race = Race::create([
            'name' => 'Carrera Test',
            'slug' => 'carrera-test',
            'event_date' => now()->addMonth()->toDateString(),
            'status' => 'registration_open',
            'discount_registration_url' => $url,
        ]);

        $race->discountParticipants()->create([
            'folio' => '2',
            'first_name' => 'Florentino ',
            'last_name' => 'Monreal Domínguez ',
        ]);

        $race->discountParticipants()->create([
            'folio' => '3',
            'first_name' => 'Ariana',
            'last_name' => 'Aldaba Morales',
        ]);

        return $race;
    }

    private function check(array $data)
    {
        return $this->postJson('/carreras/carrera-test/descuento', $data);
    }

    public function test_solo_folio_recibe_enlace(): void
    {
        $this->makeRace();

        $this->check(['folio' => ' 2 '])
            ->assertOk()
            ->assertJson(['eligible' => true, 'url' => self::URL]);
    }

    public function test_solo_nombre_completo_recibe_enlace(): void
    {
        $this->makeRace();

        $this->check(['full_name' => 'florentino  MONREAL dominguez'])
            ->assertOk()
            ->assertJson(['eligible' => true, 'url' => self::URL]);
    }

    public function test_folio_y_nombre_de_la_misma_persona_recibe_enlace(): void
    {
        $this->makeRace();

        $this->check(['folio' => '2', 'full_name' => 'Florentino Monreal Domínguez'])
            ->assertOk()
            ->assertJson(['eligible' => true]);
    }

    public function test_folio_y_nombre_de_personas_distintas_no_aplica(): void
    {
        $this->makeRace();

        $this->check(['folio' => '2', 'full_name' => 'Ariana Aldaba Morales'])
            ->assertOk()
            ->assertJson(['eligible' => false, 'url' => null]);
    }

    public function test_nombre_incompleto_no_aplica(): void
    {
        $this->makeRace();

        $this->check(['full_name' => 'Florentino Monreal'])
            ->assertOk()
            ->assertJson(['eligible' => false]);
    }

    public function test_folio_es_exacto(): void
    {
        $this->makeRace();

        $this->check(['folio' => '02'])
            ->assertOk()
            ->assertJson(['eligible' => false]);
    }

    public function test_folio_inexistente_no_aplica(): void
    {
        $this->makeRace();

        $this->check(['folio' => '999'])
            ->assertOk()
            ->assertJson(['eligible' => false, 'url' => null]);
    }

    public function test_sin_enlace_configurado_no_aplica(): void
    {
        $this->makeRace(null);

        $this->check(['folio' => '2'])
            ->assertOk()
            ->assertJson(['eligible' => false]);
    }

    public function test_sin_folio_ni_nombre_es_rechazado(): void
    {
        $this->makeRace();

        $this->check(['folio' => ' ', 'full_name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['folio', 'full_name']);
    }

    public function test_pagina_de_descuentos_carga(): void
    {
        $this->get('/descuentos')->assertOk();
    }

    public function test_busqueda_global_por_nombre(): void
    {
        $this->makeRace();

        $this->postJson('/descuentos', ['full_name' => 'Ariana Aldaba Morales'])
            ->assertOk()
            ->assertExactJson([
                'races' => [[
                    'name' => 'Carrera Test',
                    'slug' => 'carrera-test',
                    'url' => self::URL,
                    'amount' => null,
                ]],
            ]);
    }

    public function test_admin_guarda_monto_y_se_muestra_en_la_carrera(): void
    {
        $race = $this->makeRace();

        $this->actingAs($this->adminUser())
            ->put("/admin/races/{$race->id}/discounts/url", [
                'discount_registration_url' => self::URL,
                'discount_amount' => '330',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(330.0, $race->fresh()->discount_amount);

        $this->get('/carreras/carrera-test')
            ->assertInertia(fn ($page) => $page
                ->where('hasDiscount', true)
                ->where('discountAmount', 330)
            );

        $this->postJson('/descuentos', ['folio' => '2'])
            ->assertJsonPath('races.0.amount', 330);
    }

    public function test_monto_negativo_es_rechazado(): void
    {
        $race = $this->makeRace();

        $this->actingAs($this->adminUser())
            ->put("/admin/races/{$race->id}/discounts/url", [
                'discount_registration_url' => self::URL,
                'discount_amount' => '-5',
            ])
            ->assertSessionHasErrors('discount_amount');
    }

    public function test_busqueda_global_por_folio(): void
    {
        $this->makeRace();

        $this->postJson('/descuentos', ['folio' => '3'])
            ->assertOk()
            ->assertJsonCount(1, 'races');
    }

    public function test_busqueda_global_sin_coincidencias(): void
    {
        $this->makeRace();

        $this->postJson('/descuentos', ['full_name' => 'Otra Persona'])
            ->assertOk()
            ->assertExactJson(['races' => []]);
    }

    public function test_busqueda_global_ignora_carreras_sin_enlace(): void
    {
        $this->makeRace(null);

        $this->postJson('/descuentos', ['folio' => '2'])
            ->assertOk()
            ->assertExactJson(['races' => []]);
    }

    public function test_enlace_no_se_expone_en_la_carrera(): void
    {
        $race = $this->makeRace();

        $this->assertArrayNotHasKey(
            'discount_registration_url',
            $race->fresh()->toArray()
        );
    }

    public function test_admin_importa_participantes_por_folio(): void
    {
        $race = $this->makeRace();

        $this->actingAs($this->adminUser())
            ->post("/admin/races/{$race->id}/discounts/import", [
                'replace' => false,
                'participants' => [
                    ['folio' => '2', 'first_name' => 'Florentino', 'last_name' => 'Monreal'],
                    ['folio' => '4', 'first_name' => 'Nora Angélica', 'last_name' => 'Lugo García'],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(3, $race->discountParticipants()->count());

        // El folio repetido se actualiza en lugar de duplicarse
        $this->assertSame(
            'FLORENTINO MONREAL',
            $race->discountParticipants()->where('folio', '2')->value('name_key'),
        );

        // Lo importado se puede buscar por nombre
        $this->check(['full_name' => 'Nora Angelica Lugo Garcia'])
            ->assertJson(['eligible' => true]);
    }

    private function adminUser(): User
    {
        Permission::findOrCreate('races.edit', 'web');

        $user = User::factory()->create(['username' => 'admin-test']);
        $user->givePermissionTo('races.edit');

        return $user;
    }
}
