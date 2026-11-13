<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CatalogosEjemploSeeder;
use Database\Seeders\EstadoAtencionSeeder;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SistemaFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_in_and_open_the_dashboard(): void
    {
        $user = $this->administrator();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password-12345',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel principal')
            ->assertSee('Atenciones de hoy');
    }

    public function test_invalid_credentials_do_not_authenticate(): void
    {
        $this->administrator();

        $this->from(route('login'))->post(route('login'), [
            'email' => 'admin@example.test',
            'password' => 'incorrecta',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');
    }

    public function test_admin_can_create_an_attention_with_a_unique_folio(): void
    {
        $this->seed([RolesPermisosSeeder::class, EstadoAtencionSeeder::class, CatalogosEjemploSeeder::class]);
        $user = $this->administrator(false);
        $this->actingAs($user);

        $jefeId = DB::table('personal')->insertGetId([
            'nombre_completo' => 'Jefa de prueba',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $secretarioId = DB::table('personal')->insertGetId([
            'nombre_completo' => 'Secretario de prueba',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = [
            'tipo_solicitante' => 'alumno',
            'solicitante_nombre' => 'Estudiante de prueba',
            'matricula' => 'TEST123',
            'asunto' => 'No puede acceder al correo',
            'jefe_id' => $jefeId,
            'secretario_id' => $secretarioId,
            'servicio_id' => DB::table('servicios')->value('id'),
            'tipo_atencion_id' => DB::table('tipos_atencion')->value('id'),
            'descripcion_problema' => 'Solicita recuperar acceso a su cuenta.',
        ];

        $this->post(route('atenciones.store'), $data)
            ->assertRedirect(route('atenciones.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('atenciones', [
            'folio' => 'SIS-'.now()->format('Y').'-000001',
            'solicitante_nombre' => 'Estudiante de prueba',
            'usuario_registro_id' => $user->id,
        ]);

        $this->post(route('atenciones.store'), $data)->assertRedirect(route('atenciones.index'));

        $this->assertDatabaseHas('atenciones', [
            'folio' => 'SIS-'.now()->format('Y').'-000002',
        ]);
    }

    private function administrator(bool $seedRoles = true): User
    {
        if ($seedRoles) {
            $this->seed(RolesPermisosSeeder::class);
        }

        $user = User::factory()->create([
            'name' => 'Administrador de prueba',
            'email' => 'admin@example.test',
            'password' => 'Password-12345',
            'activo' => true,
        ]);
        $user->assignRole('Administrador');

        return $user;
    }
}
