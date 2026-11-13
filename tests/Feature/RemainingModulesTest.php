<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CarreraSeeder;
use Database\Seeders\CatalogosEjemploSeeder;
use Database\Seeders\EstadoAtencionSeeder;
use Database\Seeders\RolDocumentoSeeder;
use Database\Seeders\RolesPermisosSeeder;
use Database\Seeders\TipoDocumentoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemainingModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_attention_state_and_upload_private_evidence(): void
    {
        Storage::fake('local');
        $this->seedBase();
        $user = $this->administrator();
        $service = DB::table('servicios')->value('id');
        $type = DB::table('tipos_atencion')->value('id');
        $person = DB::table('personal')->insertGetId([
            'nombre_completo' => 'Personal de prueba',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $attention = DB::table('atenciones')->insertGetId([
            'folio' => 'SIS-'.now()->format('Y').'-000001',
            'fecha_hora' => now(),
            'tipo_solicitante' => 'alumno',
            'solicitante_nombre' => 'Alumno de prueba',
            'asunto' => 'Falla de red',
            'jefe_id' => $person,
            'secretario_id' => $person,
            'servicio_id' => $service,
            'tipo_atencion_id' => $type,
            'descripcion_problema' => 'No conecta a la red.',
            'estado_id' => DB::table('estados_atencion')->where('nombre', 'Registrada')->value('id'),
            'usuario_registro_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get(route('atenciones.show', $attention))->assertOk()->assertSee('Falla de red');
        $this->put(route('atenciones.update', $attention), [
            'estado_id' => DB::table('estados_atencion')->where('nombre', 'En proceso')->value('id'),
            'comentario' => 'Se revisa el punto de acceso.',
        ])->assertRedirect(route('atenciones.show', $attention));
        $this->assertDatabaseHas('atencion_seguimientos', [
            'atencion_id' => $attention,
            'comentario' => 'Se revisa el punto de acceso.',
        ]);
        $this->put(route('atenciones.update', $attention), [
            'estado_id' => DB::table('estados_atencion')->where('nombre', 'Resuelta')->value('id'),
            'comentario' => 'Problema resuelto.',
        ])->assertRedirect(route('atenciones.show', $attention));
        $this->assertNotNull(DB::table('atenciones')->where('id', $attention)->value('hora_salida'));
        $this->put(route('atenciones.update', $attention), [
            'estado_id' => DB::table('estados_atencion')->where('nombre', 'Reabierta')->value('id'),
            'comentario' => 'Se reabre el caso.',
        ])->assertRedirect(route('atenciones.show', $attention));
        $this->assertNull(DB::table('atenciones')->where('id', $attention)->value('hora_salida'));

        $this->post(route('atenciones.evidencias.upload', $attention), [
            'archivo' => UploadedFile::fake()->createWithContent('evidencia.pdf', "%PDF-1.4\nPrueba de evidencia\n%%EOF"),
        ])->assertRedirect(route('atenciones.show', $attention));
        $adjunto = DB::table('atencion_adjuntos')->where('atencion_id', $attention)->first();
        $this->assertNotNull($adjunto);
        Storage::disk('local')->assertExists($adjunto->ruta_archivo);
        $this->get(route('atenciones.evidencias.download', [$attention, $adjunto->id]))->assertOk();
    }

    public function test_admin_can_register_student_and_academic_processes(): void
    {
        $this->seedBase();
        $user = $this->administrator();
        $career = DB::table('carreras')->value('id');
        $this->actingAs($user);

        $this->post(route('estudiantes.store'), [
            'nombre_completo' => 'Estudiante de prueba',
            'matricula' => 'A01234567',
            'carrera_id' => $career,
            'semestre' => 8,
            'estatus' => 'activo',
        ])->assertRedirect();
        $student = DB::table('estudiantes')->where('matricula', 'A01234567')->first();
        $this->get(route('estudiantes.show', $student->id))->assertOk()->assertSee('Estudiante de prueba');

        $this->post(route('estudiantes.residencias.store', $student->id), [
            'proyecto' => 'Sistema de monitoreo',
            'empresa_institucion' => 'Instituto',
            'fecha_inicio' => '2026-01-01',
            'fecha_termino' => '2026-06-30',
            'horas' => 500,
            'estado' => 'en_curso',
        ])->assertRedirect();
        $this->post(route('estudiantes.servicio-social.store', $student->id), [
            'institucion_receptora' => 'Centro comunitario',
            'fecha_inicio' => '2026-01-01',
            'fecha_termino' => '2026-06-30',
            'horas' => 480,
            'estado' => 'en_curso',
        ])->assertRedirect();
        $this->post(route('estudiantes.actividades.store', $student->id), [
            'tipo_actividad' => 'Deportiva',
            'nombre_actividad' => 'Torneo institucional',
            'periodo_escolar' => '2026-1',
            'creditos' => 1.0,
        ])->assertRedirect();

        $this->get(route('seguimiento.index'))->assertOk()->assertSee('Sistema de monitoreo')->assertSee('Centro comunitario');
        $this->assertDatabaseHas('tramites_actividad_complementaria', ['estudiante_id' => $student->id, 'creditos' => 1]);
    }

    public function test_admin_can_generate_and_download_an_archived_word_document(): void
    {
        Storage::fake('local');
        $this->seedBase();
        $user = $this->administrator();
        $studentId = DB::table('estudiantes')->insertGetId([
            'nombre_completo' => 'Estudiante de documentos',
            'matricula' => 'D001',
            'carrera_id' => DB::table('carreras')->value('id'),
            'estatus' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tramiteId = DB::table('tramites_residencia')->insertGetId([
            'estudiante_id' => $studentId,
            'proyecto' => 'Automatización',
            'empresa_institucion' => 'TESI',
            'fecha_inicio' => '2026-01-01',
            'fecha_termino' => '2026-06-30',
            'horas' => 500,
            'estado' => 'en_curso',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $typeId = DB::table('tipos_documento')->where('clave', 'RES_ACEP')->value('id');
        $blocks = DB::table('bloques_firma')->where('tipo_documento_id', $typeId)->get();
        $firmantes = [];
        foreach ($blocks as $block) {
            $personId = DB::table('personal')->insertGetId([
                'nombre_completo' => 'Firmante '.$block->id,
                'puesto_actual' => 'Responsable',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('personal_rol')->insert(['personal_id' => $personId, 'rol_id' => $block->rol_id, 'activo' => true]);
            $firmantes[$block->id] = $personId;
        }
        $this->actingAs($user);

        $this->post(route('documentos.store'), [
            'tipo_documento_id' => $typeId,
            'estudiante_id' => $studentId,
            'tramite_type' => 'tramites_residencia',
            'tramite_id' => $tramiteId,
            'destinatario' => 'Jefatura de División',
            'firmantes' => $firmantes,
        ])->assertRedirect(route('documentos.index'));

        $documento = DB::table('documentos_generados')->first();
        $this->assertNotNull($documento);
        $this->assertSame('ISC-RES-ACEP-'.now()->format('Y').'-000001', $documento->folio);
        Storage::disk('local')->assertExists($documento->ruta_archivo);
        $this->assertSame(hash_file('sha256', Storage::disk('local')->path($documento->ruta_archivo)), $documento->hash_archivo);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('local')->path($documento->ruta_archivo)) === true);
        $this->assertStringContainsString('Estudiante de documentos', $zip->getFromName('word/document.xml'));
        $zip->close();
        $this->get(route('documentos.download', $documento->id))->assertOk();
        $this->assertDatabaseCount('documento_firmante', count($blocks));
    }

    public function test_admin_can_create_users_and_catalog_entries(): void
    {
        $this->seedBase();
        $user = $this->administrator();
        $this->actingAs($user);

        $this->post(route('usuarios.store'), [
            'name' => 'Usuario nuevo',
            'email' => 'nuevo@example.test',
            'password' => 'NewPassword-123',
            'password_confirmation' => 'NewPassword-123',
            'role' => 'Secretario',
        ])->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.test']);
        $newUser = User::where('email', 'nuevo@example.test')->firstOrFail();
        $this->put(route('usuarios.update', $newUser->id), [
            'name' => 'Usuario actualizado',
            'email' => 'nuevo@example.test',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'Secretario',
            'personal_id' => '',
            'activo' => '0',
        ])->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['id' => $newUser->id, 'name' => 'Usuario actualizado', 'activo' => false]);

        $this->post(route('catalogos.store', 'periodos_escolares'), [
            'nombre' => '2026-2',
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-12-31',
            'activo' => 1,
        ])->assertRedirect(route('catalogos.index'));
        $periodoId = DB::table('periodos_escolares')->where('nombre', '2026-2')->value('id');
        $this->assertDatabaseHas('periodos_escolares', ['id' => $periodoId, 'activo' => true]);
        $this->patch(route('catalogos.toggle', ['catalogo' => 'periodos_escolares', 'id' => $periodoId]))->assertRedirect(route('catalogos.index'));
        $this->assertDatabaseHas('periodos_escolares', ['id' => $periodoId, 'activo' => false]);
        $this->get(route('catalogos.index'))->assertOk()->assertSee('2026-2');
    }

    public function test_admin_can_render_all_management_screens(): void
    {
        $this->seedBase();
        $user = $this->administrator();
        $this->actingAs($user);

        foreach ([
            route('dashboard'),
            route('atenciones.index'),
            route('atenciones.create'),
            route('personal.index'),
            route('estudiantes.index'),
            route('estudiantes.create'),
            route('seguimiento.index'),
            route('documentos.index'),
            route('documentos.create'),
            route('usuarios.index'),
            route('catalogos.index'),
            route('auditoria.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('atenciones.index', ['q' => ['no válido']]))->assertSessionHasErrors('q');
        $this->get(route('estudiantes.index', ['q' => ['no válido']]))->assertSessionHasErrors('q');
    }

    public function test_secretary_cannot_read_or_change_student_contact_fields(): void
    {
        $this->seedBase();
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole('Secretario');
        $studentId = DB::table('estudiantes')->insertGetId([
            'nombre_completo' => 'Estudiante privado',
            'matricula' => 'P001',
            'carrera_id' => DB::table('carreras')->value('id'),
            'correo_institucional' => 'privado@example.test',
            'telefono' => '5551234567',
            'estatus' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);
        $this->get(route('usuarios.index'))->assertForbidden();
        $this->get(route('catalogos.index'))->assertForbidden();

        $this->get(route('estudiantes.show', $studentId))
            ->assertOk()
            ->assertDontSee('privado@example.test')
            ->assertDontSee('5551234567');

        $this->put(route('estudiantes.update', $studentId), [
            'nombre_completo' => 'Estudiante privado actualizado',
            'matricula' => 'P001',
            'carrera_id' => DB::table('carreras')->value('id'),
            'estatus' => 'activo',
            'correo_institucional' => 'atacante@example.test',
            'telefono' => '1111111111',
        ])->assertRedirect(route('estudiantes.show', $studentId));

        $this->assertDatabaseHas('estudiantes', [
            'id' => $studentId,
            'correo_institucional' => 'privado@example.test',
            'telefono' => '5551234567',
        ]);
    }

    private function seedBase(): void
    {
        $this->seed([
            CarreraSeeder::class,
            EstadoAtencionSeeder::class,
            RolDocumentoSeeder::class,
            TipoDocumentoSeeder::class,
            RolesPermisosSeeder::class,
            CatalogosEjemploSeeder::class,
        ]);
    }

    private function administrator(): User
    {
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole('Administrador');

        return $user;
    }
}
