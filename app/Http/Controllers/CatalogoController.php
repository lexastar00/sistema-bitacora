<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function index(): View
    {
        return view('catalogos.index', [
            'departamentos' => DB::table('departamentos')->orderBy('nombre')->get(),
            'servicios' => DB::table('servicios')->join('departamentos', 'departamentos.id', '=', 'servicios.departamento_id')->select('servicios.*', 'departamentos.nombre as departamento')->orderBy('servicios.nombre')->get(),
            'departamentosActivos' => DB::table('departamentos')->where('activo', true)->orderBy('nombre')->get(),
            'tiposAtencion' => DB::table('tipos_atencion')->orderBy('nombre')->get(),
            'tiposProblema' => DB::table('tipos_problema')->orderBy('nombre')->get(),
            'periodos' => DB::table('periodos_escolares')->orderByDesc('fecha_inicio')->get(),
            'carreras' => DB::table('carreras')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, string $catalogo): RedirectResponse
    {
        abort_unless(in_array($catalogo, ['carreras', 'departamentos', 'servicios', 'tipos_atencion', 'tipos_problema', 'periodos_escolares'], true), 404);
        $datos = match ($catalogo) {
            'carreras', 'departamentos' => $request->validate([
                'nombre' => ['required', 'string', 'max:150', Rule::unique($catalogo, 'nombre')],
            ]),
            'servicios' => $request->validate([
                'nombre' => ['required', 'string', 'max:150', Rule::unique('servicios', 'nombre')->where('departamento_id', $request->input('departamento_id'))],
                'departamento_id' => ['required', 'integer', Rule::exists('departamentos', 'id')->where('activo', true)],
            ]),
            'tipos_atencion', 'tipos_problema' => $request->validate([
                'nombre' => ['required', 'string', 'max:150', Rule::unique($catalogo, 'nombre')],
            ]),
            'periodos_escolares' => $request->validate([
                'nombre' => ['required', 'string', 'max:30', Rule::unique('periodos_escolares', 'nombre')],
                'fecha_inicio' => ['required', 'date'],
                'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
                'activo' => ['nullable', 'boolean'],
            ]),
        };

        if ($catalogo === 'periodos_escolares') {
            $id = DB::transaction(function () use ($datos): int {
                $activo = (bool) ($datos['activo'] ?? false);
                if ($activo) {
                    DB::table('periodos_escolares')->update(['activo' => false]);
                }

                return DB::table('periodos_escolares')->insertGetId([
                    'nombre' => $datos['nombre'],
                    'fecha_inicio' => $datos['fecha_inicio'],
                    'fecha_fin' => $datos['fecha_fin'],
                    'activo' => $activo,
                ]);
            });
        } else {
            $datos['activo'] = true;
            $id = DB::table($catalogo)->insertGetId($datos);
        }
        $this->auditar('Catálogo creado: '.$catalogo, $catalogo, $id);

        return redirect()->route('catalogos.index')->with('status', 'Catálogo actualizado.');
    }

    public function toggle(string $catalogo, int $id): RedirectResponse
    {
        abort_unless(in_array($catalogo, ['carreras', 'departamentos', 'servicios', 'tipos_atencion', 'tipos_problema', 'periodos_escolares'], true), 404);
        $registro = DB::table($catalogo)->where('id', $id)->first();
        abort_unless($registro, 404);

        $activo = ! (bool) $registro->activo;
        if ($catalogo === 'periodos_escolares' && $activo) {
            DB::transaction(function () use ($catalogo, $id): void {
                DB::table($catalogo)->update(['activo' => false]);
                DB::table($catalogo)->where('id', $id)->update(['activo' => true]);
            });
        } else {
            DB::table($catalogo)->where('id', $id)->update(['activo' => $activo]);
        }
        $this->auditar('Estado de catálogo actualizado: '.$catalogo, $catalogo, $id);

        return redirect()->route('catalogos.index')->with('status', 'Estado del catálogo actualizado.');
    }

    private function auditar(string $descripcion, string $tabla, int $id): void
    {
        DB::table('activity_log')->insert([
            'log_name' => 'catalogos',
            'description' => $descripcion,
            'subject_type' => $tabla,
            'event' => 'updated',
            'subject_id' => $id,
            'causer_type' => User::class,
            'causer_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
