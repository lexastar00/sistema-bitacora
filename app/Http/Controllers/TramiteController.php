<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TramiteController extends Controller
{
    public function residencia(Request $request, int $estudiante): RedirectResponse
    {
        $this->verificarEstudiante($estudiante);
        $datos = $request->validate([
            'proyecto' => ['required', 'string', 'max:255'],
            'empresa_institucion' => ['required', 'string', 'max:255'],
            'asesor_interno_id' => ['nullable', 'integer', Rule::exists('personal', 'id')->where('activo', true)],
            'asesor_externo' => ['nullable', 'string', 'max:200'],
            'periodo_id' => ['nullable', 'integer', Rule::exists('periodos_escolares', 'id')],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'horas' => ['required', 'integer', 'min:1', 'max:65535'],
            'estado' => ['required', Rule::in(['en_curso', 'terminado', 'cancelado'])],
        ]);

        DB::table('tramites_residencia')->insert($datos + ['estudiante_id' => $estudiante, 'created_at' => now(), 'updated_at' => now()]);
        $this->auditar('Trámite de residencia registrado', 'tramites_residencia', $estudiante, $request);

        return back()->with('status', 'Trámite de residencia registrado.');
    }

    public function servicioSocial(Request $request, int $estudiante): RedirectResponse
    {
        $this->verificarEstudiante($estudiante);
        $datos = $request->validate([
            'institucion_receptora' => ['required', 'string', 'max:255'],
            'programa_actividad' => ['nullable', 'string', 'max:255'],
            'periodo_id' => ['nullable', 'integer', Rule::exists('periodos_escolares', 'id')],
            'fecha_inicio' => ['required', 'date'],
            'fecha_termino' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'horas' => ['required', 'integer', 'min:1', 'max:65535'],
            'estado' => ['required', Rule::in(['en_curso', 'terminado', 'cancelado'])],
        ]);

        DB::table('tramites_servicio_social')->insert($datos + ['estudiante_id' => $estudiante, 'created_at' => now(), 'updated_at' => now()]);
        $this->auditar('Trámite de servicio social registrado', 'tramites_servicio_social', $estudiante, $request);

        return back()->with('status', 'Trámite de servicio social registrado.');
    }

    public function actividad(Request $request, int $estudiante): RedirectResponse
    {
        $this->verificarEstudiante($estudiante);
        $datos = $request->validate([
            'tipo_actividad' => ['required', 'string', 'max:100'],
            'nombre_actividad' => ['required', 'string', 'max:255'],
            'periodo_escolar' => ['required', 'string', 'max:20'],
            'creditos' => ['required', 'numeric', 'gt:0', 'lte:99.9'],
        ]);

        DB::table('tramites_actividad_complementaria')->insert($datos + ['estudiante_id' => $estudiante, 'created_at' => now(), 'updated_at' => now()]);
        $this->auditar('Actividad complementaria registrada', 'tramites_actividad_complementaria', $estudiante, $request);

        return back()->with('status', 'Actividad complementaria registrada.');
    }

    public function actualizarResidencia(Request $request, int $estudiante, int $tramite): RedirectResponse
    {
        $this->verificarEstudiante($estudiante);
        $datos = $request->validate(['estado' => ['required', Rule::in(['en_curso', 'terminado', 'cancelado'])]]);
        $actualizado = DB::table('tramites_residencia')
            ->where('id', $tramite)
            ->where('estudiante_id', $estudiante)
            ->update($datos + ['updated_at' => now()]);
        abort_unless($actualizado, 404);
        $this->auditar('Estado de residencia actualizado', 'tramites_residencia', $tramite, $request);

        return back()->with('status', 'Estado de residencia actualizado.');
    }

    public function actualizarServicioSocial(Request $request, int $estudiante, int $tramite): RedirectResponse
    {
        $this->verificarEstudiante($estudiante);
        $datos = $request->validate(['estado' => ['required', Rule::in(['en_curso', 'terminado', 'cancelado'])]]);
        $actualizado = DB::table('tramites_servicio_social')
            ->where('id', $tramite)
            ->where('estudiante_id', $estudiante)
            ->update($datos + ['updated_at' => now()]);
        abort_unless($actualizado, 404);
        $this->auditar('Estado de servicio social actualizado', 'tramites_servicio_social', $tramite, $request);

        return back()->with('status', 'Estado de servicio social actualizado.');
    }

    private function verificarEstudiante(int $id): void
    {
        abort_unless(DB::table('estudiantes')->where('id', $id)->exists(), 404);
    }

    private function auditar(string $descripcion, string $tabla, int $id, Request $request): void
    {
        DB::table('activity_log')->insert([
            'log_name' => 'tramites',
            'description' => $descripcion,
            'subject_type' => $tabla,
            'event' => 'created',
            'subject_id' => $id,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
