<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtencionController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer', Rule::exists('estados_atencion', 'id')],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $atenciones = DB::table('atenciones')
            ->join('estados_atencion', 'estados_atencion.id', '=', 'atenciones.estado_id')
            ->join('servicios', 'servicios.id', '=', 'atenciones.servicio_id')
            ->select(
                'atenciones.id',
                'atenciones.folio',
                'atenciones.fecha_hora',
                'atenciones.solicitante_nombre',
                'atenciones.asunto',
                'estados_atencion.nombre as estado',
                'servicios.nombre as servicio'
            )
            ->when($filtros['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('atenciones.folio', 'like', "%{$search}%")
                        ->orWhere('atenciones.solicitante_nombre', 'like', "%{$search}%")
                        ->orWhere('atenciones.matricula', 'like', "%{$search}%")
                        ->orWhere('atenciones.asunto', 'like', "%{$search}%");
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, string $estado) => $query->where('estados_atencion.id', $estado))
            ->when($filtros['desde'] ?? null, fn ($query, string $desde) => $query->whereDate('atenciones.fecha_hora', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($query, string $hasta) => $query->whereDate('atenciones.fecha_hora', '<=', $hasta))
            ->latest('atenciones.fecha_hora')
            ->paginate(15)
            ->withQueryString();

        return view('atenciones.index', [
            'atenciones' => $atenciones,
            'estados' => DB::table('estados_atencion')->where('activo', true)->orderBy('orden')->get(),
        ]);
    }

    public function show(int $atencion): View
    {
        $registro = $this->atencion($atencion);
        $seguimientos = DB::table('atencion_seguimientos')
            ->join('users', 'users.id', '=', 'atencion_seguimientos.usuario_id')
            ->leftJoin('estados_atencion as anterior', 'anterior.id', '=', 'atencion_seguimientos.estado_anterior_id')
            ->join('estados_atencion as nuevo', 'nuevo.id', '=', 'atencion_seguimientos.estado_nuevo_id')
            ->where('atencion_seguimientos.atencion_id', $atencion)
            ->select('atencion_seguimientos.*', 'users.name as usuario', 'anterior.nombre as estado_anterior', 'nuevo.nombre as estado_nuevo')
            ->latest('atencion_seguimientos.fecha')
            ->get();
        $adjuntos = DB::table('atencion_adjuntos')
            ->where('atencion_id', $atencion)
            ->orderByDesc('fecha_subida')
            ->get();

        return view('atenciones.show', [
            'atencion' => $registro,
            'seguimientos' => $seguimientos,
            'adjuntos' => $adjuntos,
            'estados' => DB::table('estados_atencion')->where('activo', true)->orderBy('orden')->get(),
            'personal' => DB::table('personal')->where('activo', true)->orderBy('nombre_completo')->get(),
        ]);
    }

    public function update(Request $request, int $atencion): RedirectResponse
    {
        abort_unless($request->user()->can('bitacora.cambiar_estado') || $request->user()->can('bitacora.reasignar'), 403);
        $registro = $this->atencion($atencion);
        $datos = $request->validate([
            'estado_id' => ['nullable', 'integer', Rule::exists('estados_atencion', 'id')->where('activo', true)],
            'atendido_por_id' => ['nullable', 'integer', Rule::exists('personal', 'id')->where('activo', true)],
            'comentario' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('estado_id', $datos)) {
            abort_unless($request->user()->can('bitacora.cambiar_estado'), 403);
        }
        if (array_key_exists('atendido_por_id', $datos)) {
            abort_unless($request->user()->can('bitacora.reasignar'), 403);
        }
        if (empty($datos['estado_id']) && empty($datos['atendido_por_id']) && empty($datos['comentario'])) {
            return back()->withErrors(['comentario' => 'Agrega un cambio o comentario para guardar el seguimiento.']);
        }

        DB::transaction(function () use ($atencion, $registro, $datos, $request): void {
            $estadoNuevo = (int) ($datos['estado_id'] ?? $registro->estado_id);
            $responsableNuevo = array_key_exists('atendido_por_id', $datos)
                ? (isset($datos['atendido_por_id']) ? (int) $datos['atendido_por_id'] : null)
                : $registro->atendido_por_id;
            $nombreEstado = DB::table('estados_atencion')->where('id', $estadoNuevo)->value('nombre');
            $horaSalida = $nombreEstado === 'Reabierta'
                ? null
                : (in_array($nombreEstado, ['Resuelta', 'Cancelada'], true) ? ($registro->hora_salida ?? now()) : $registro->hora_salida);

            DB::table('atenciones')->where('id', $atencion)->update([
                'estado_id' => $estadoNuevo,
                'atendido_por_id' => $responsableNuevo,
                'hora_salida' => $horaSalida,
                'updated_at' => now(),
            ]);

            DB::table('atencion_seguimientos')->insert([
                'atencion_id' => $atencion,
                'usuario_id' => $request->user()->id,
                'estado_anterior_id' => $registro->estado_id,
                'estado_nuevo_id' => $estadoNuevo,
                'responsable_anterior_id' => $registro->atendido_por_id,
                'responsable_nuevo_id' => $responsableNuevo,
                'comentario' => $datos['comentario'] ?? null,
                'fecha' => now(),
            ]);
            DB::table('activity_log')->insert([
                'log_name' => 'bitacora',
                'description' => 'Seguimiento actualizado para '.$registro->folio,
                'subject_type' => 'atenciones',
                'event' => 'updated',
                'subject_id' => $atencion,
                'causer_type' => User::class,
                'causer_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('atenciones.show', $atencion)->with('status', 'Seguimiento guardado.');
    }

    public function upload(Request $request, int $atencion): RedirectResponse
    {
        abort_unless($request->user()->can('bitacora.adjuntar_evidencia'), 403);
        $registro = $this->atencion($atencion);
        $datos = $request->validate([
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $archivo = $datos['archivo'];
        $ruta = $archivo->store("atenciones/{$atencion}", 'local');
        DB::table('atencion_adjuntos')->insert([
            'atencion_id' => $atencion,
            'nombre_archivo' => mb_substr($archivo->getClientOriginalName(), 0, 255),
            'ruta_archivo' => $ruta,
            'tipo_mime' => $archivo->getMimeType() ?: 'application/octet-stream',
            'tamano_kb' => (int) ceil($archivo->getSize() / 1024),
            'usuario_id' => $request->user()->id,
            'fecha_subida' => now(),
        ]);
        DB::table('activity_log')->insert([
            'log_name' => 'bitacora',
            'description' => 'Evidencia adjuntada a '.$registro->folio,
            'subject_type' => 'atenciones',
            'event' => 'attached',
            'subject_id' => $atencion,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('atenciones.show', $atencion)->with('status', 'Evidencia adjuntada correctamente.');
    }

    public function download(int $atencion, int $adjunto): StreamedResponse
    {
        abort_unless(auth()->user()->can('bitacora.ver'), 403);
        $archivo = DB::table('atencion_adjuntos')->where('id', $adjunto)->where('atencion_id', $atencion)->first();
        abort_unless($archivo && Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        return Storage::disk('local')->download($archivo->ruta_archivo, basename($archivo->nombre_archivo));
    }

    public function create(): View
    {
        return view('atenciones.create', [
            'personal' => DB::table('personal')->where('activo', true)->orderBy('nombre_completo')->get(),
            'servicios' => DB::table('servicios')->where('activo', true)->orderBy('nombre')->get(),
            'tiposAtencion' => DB::table('tipos_atencion')->where('activo', true)->orderBy('nombre')->get(),
            'tiposProblema' => DB::table('tipos_problema')->where('activo', true)->orderBy('nombre')->get(),
            'sinPersonal' => ! DB::table('personal')->where('activo', true)->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_solicitante' => ['required', Rule::in(['alumno', 'docente', 'administrativo', 'otro'])],
            'solicitante_nombre' => ['required', 'string', 'max:200'],
            'matricula' => ['nullable', 'string', 'max:20'],
            'asunto' => ['required', 'string', 'max:255'],
            'jefe_id' => ['required', 'integer', Rule::exists('personal', 'id')->where('activo', true)],
            'secretario_id' => ['required', 'integer', Rule::exists('personal', 'id')->where('activo', true)],
            'servicio_id' => ['required', 'integer', Rule::exists('servicios', 'id')->where('activo', true)],
            'tipo_atencion_id' => ['required', 'integer', Rule::exists('tipos_atencion', 'id')->where('activo', true)],
            'tipo_problema_id' => ['nullable', 'integer', Rule::exists('tipos_problema', 'id')->where('activo', true)],
            'descripcion_problema' => ['required', 'string', 'max:10000'],
            'observaciones' => ['nullable', 'string', 'max:10000'],
        ]);

        $folio = DB::transaction(function () use ($datos, $request): string {
            $anio = (int) now()->format('Y');
            DB::table('folios_atencion')->insertOrIgnore(['anio' => $anio, 'ultimo_numero' => 0]);

            $contador = DB::table('folios_atencion')->where('anio', $anio)->lockForUpdate()->first();
            $siguiente = $contador->ultimo_numero + 1;
            DB::table('folios_atencion')->where('anio', $anio)->update(['ultimo_numero' => $siguiente]);

            $estadoId = DB::table('estados_atencion')->where('nombre', 'Registrada')->value('id');
            if (! $estadoId) {
                throw new \RuntimeException('No existe el estado inicial "Registrada". Ejecuta los seeders de la base de datos.');
            }

            $tipoProblemaId = $datos['tipo_problema_id'] ?? null;

            $folio = sprintf('SIS-%d-%06d', $anio, $siguiente);
            $atencionId = DB::table('atenciones')->insertGetId([
                'folio' => $folio,
                'fecha_hora' => now(),
                'tipo_solicitante' => $datos['tipo_solicitante'],
                'solicitante_nombre' => $datos['solicitante_nombre'],
                'matricula' => $datos['matricula'] ?? null,
                'asunto' => $datos['asunto'],
                'jefe_id' => $datos['jefe_id'],
                'secretario_id' => $datos['secretario_id'],
                'atendido_por_id' => $request->user()->personal_id,
                'servicio_id' => $datos['servicio_id'],
                'tipo_atencion_id' => $datos['tipo_atencion_id'],
                'tipo_problema_id' => $tipoProblemaId,
                'descripcion_problema' => $datos['descripcion_problema'],
                'observaciones' => $datos['observaciones'] ?? null,
                'estado_id' => $estadoId,
                'usuario_registro_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('atencion_seguimientos')->insert([
                'atencion_id' => $atencionId,
                'usuario_id' => $request->user()->id,
                'estado_nuevo_id' => $estadoId,
                'responsable_nuevo_id' => $request->user()->personal_id,
                'comentario' => 'Atención registrada.',
                'fecha' => now(),
            ]);
            DB::table('activity_log')->insert([
                'log_name' => 'bitacora',
                'description' => 'Atención registrada: '.$folio,
                'subject_type' => 'atenciones',
                'event' => 'created',
                'subject_id' => $atencionId,
                'causer_type' => User::class,
                'causer_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $folio;
        });

        return redirect()->route('atenciones.index')->with('status', "Atención {$folio} registrada correctamente.");
    }

    private function atencion(int $id): object
    {
        $registro = DB::table('atenciones')
            ->join('estados_atencion', 'estados_atencion.id', '=', 'atenciones.estado_id')
            ->join('servicios', 'servicios.id', '=', 'atenciones.servicio_id')
            ->join('users', 'users.id', '=', 'atenciones.usuario_registro_id')
            ->where('atenciones.id', $id)
            ->select('atenciones.*', 'estados_atencion.nombre as estado', 'servicios.nombre as servicio', 'users.name as usuario_registro')
            ->first();

        abort_unless($registro, 404);

        return $registro;
    }
}
