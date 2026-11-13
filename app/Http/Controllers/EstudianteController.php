<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EstudianteController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'estatus' => ['nullable', Rule::in(['activo', 'egresado', 'baja'])],
        ]);
        $incluirContacto = auth()->user()->can('seguimiento.ver_contacto');
        $columnas = [
            'estudiantes.id',
            'estudiantes.nombre_completo',
            'estudiantes.matricula',
            'estudiantes.semestre',
            'estudiantes.estatus',
            'carreras.nombre as carrera',
            'periodos_escolares.nombre as periodo',
        ];
        if ($incluirContacto) {
            $columnas[] = 'estudiantes.correo_institucional';
            $columnas[] = 'estudiantes.telefono';
        }
        $estudiantes = DB::table('estudiantes')
            ->join('carreras', 'carreras.id', '=', 'estudiantes.carrera_id')
            ->leftJoin('periodos_escolares', 'periodos_escolares.id', '=', 'estudiantes.periodo_id')
            ->select($columnas)
            ->when($filtros['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('estudiantes.nombre_completo', 'like', "%{$search}%")
                        ->orWhere('estudiantes.matricula', 'like', "%{$search}%");
                });
            })
            ->when($filtros['estatus'] ?? null, fn ($query, string $estatus) => $query->where('estudiantes.estatus', $estatus))
            ->orderBy('estudiantes.nombre_completo')
            ->paginate(20)
            ->withQueryString();

        return view('estudiantes.index', compact('estudiantes', 'incluirContacto'));
    }

    public function create(): View
    {
        return view('estudiantes.create', $this->catalogos());
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $id = DB::table('estudiantes')->insertGetId($datos + ['created_at' => now(), 'updated_at' => now()]);
        $this->auditar('Estudiante registrado', 'estudiantes', $id, $request);

        return redirect()->route('estudiantes.show', $id)->with('status', 'Estudiante registrado.');
    }

    public function show(int $estudiante): View
    {
        $registro = $this->estudiante($estudiante);
        $tramites = [
            'residencias' => DB::table('tramites_residencia')->where('estudiante_id', $estudiante)->latest()->get(),
            'servicioSocial' => DB::table('tramites_servicio_social')->where('estudiante_id', $estudiante)->latest()->get(),
            'actividades' => DB::table('tramites_actividad_complementaria')->where('estudiante_id', $estudiante)->latest()->get(),
        ];

        return view('estudiantes.show', [
            'estudiante' => $registro,
            'tramites' => $tramites,
            'periodos' => DB::table('periodos_escolares')->orderByDesc('fecha_inicio')->get(),
            'personal' => DB::table('personal')->where('activo', true)->orderBy('nombre_completo')->get(),
            'incluirContacto' => auth()->user()->can('seguimiento.ver_contacto'),
        ]);
    }

    public function update(Request $request, int $estudiante): RedirectResponse
    {
        $this->estudiante($estudiante);
        $datos = $this->validar($request, $estudiante);
        DB::table('estudiantes')->where('id', $estudiante)->update($datos + ['updated_at' => now()]);
        $this->auditar('Datos de estudiante actualizados', 'estudiantes', $estudiante, $request);

        return redirect()->route('estudiantes.show', $estudiante)->with('status', 'Datos del estudiante actualizados.');
    }

    private function validar(Request $request, ?int $estudiante = null): array
    {
        $reglas = [
            'nombre_completo' => ['required', 'string', 'max:200'],
            'matricula' => ['required', 'string', 'max:20', Rule::unique('estudiantes', 'matricula')->ignore($estudiante)],
            'carrera_id' => ['required', 'integer', Rule::exists('carreras', 'id')],
            'semestre' => ['nullable', 'integer', 'between:1,16'],
            'periodo_id' => ['nullable', 'integer', Rule::exists('periodos_escolares', 'id')],
            'estatus' => ['required', Rule::in(['activo', 'egresado', 'baja'])],
        ];
        if (auth()->user()->can('seguimiento.ver_contacto')) {
            $reglas['correo_institucional'] = ['nullable', 'email', 'max:150'];
            $reglas['telefono'] = ['nullable', 'string', 'max:20'];
        }

        return $request->validate($reglas);
    }

    private function estudiante(int $id): object
    {
        $registro = DB::table('estudiantes')
            ->join('carreras', 'carreras.id', '=', 'estudiantes.carrera_id')
            ->leftJoin('periodos_escolares', 'periodos_escolares.id', '=', 'estudiantes.periodo_id')
            ->where('estudiantes.id', $id)
            ->select('estudiantes.*', 'carreras.nombre as carrera', 'periodos_escolares.nombre as periodo')
            ->first();

        abort_unless($registro, 404);

        return $registro;
    }

    private function catalogos(): array
    {
        return [
            'carreras' => DB::table('carreras')->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => DB::table('periodos_escolares')->orderByDesc('fecha_inicio')->get(),
        ];
    }

    private function auditar(string $descripcion, string $tabla, int $id, Request $request): void
    {
        DB::table('activity_log')->insert([
            'log_name' => 'estudiantes',
            'description' => $descripcion,
            'subject_type' => $tabla,
            'event' => 'updated',
            'subject_id' => $id,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
