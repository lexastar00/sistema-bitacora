<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DocumentoWord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function index(): View
    {
        $documentos = DB::table('documentos_generados')
            ->join('tipos_documento', 'tipos_documento.id', '=', 'documentos_generados.tipo_documento_id')
            ->join('estudiantes', 'estudiantes.id', '=', 'documentos_generados.estudiante_id')
            ->join('users', 'users.id', '=', 'documentos_generados.usuario_id')
            ->select('documentos_generados.*', 'tipos_documento.nombre as tipo', 'estudiantes.nombre_completo as estudiante', 'estudiantes.matricula', 'users.name as usuario')
            ->latest('documentos_generados.fecha_generacion')
            ->paginate(20);

        return view('documentos.index', compact('documentos'));
    }

    public function create(): View
    {
        return view('documentos.create', [
            'tipos' => DB::table('tipos_documento')->where('activo', true)->orderBy('nombre')->get(),
            'estudiantes' => DB::table('estudiantes')->where('estatus', '!=', 'baja')->orderBy('nombre_completo')->get(),
            'tramites' => [
                'residencias' => DB::table('tramites_residencia')->join('estudiantes', 'estudiantes.id', '=', 'tramites_residencia.estudiante_id')->select('tramites_residencia.id', 'tramites_residencia.estudiante_id', 'tramites_residencia.proyecto as descripcion', 'estudiantes.nombre_completo', 'estudiantes.matricula', 'tramites_residencia.estado')->orderByDesc('tramites_residencia.created_at')->get(),
                'servicioSocial' => DB::table('tramites_servicio_social')->join('estudiantes', 'estudiantes.id', '=', 'tramites_servicio_social.estudiante_id')->select('tramites_servicio_social.id', 'tramites_servicio_social.estudiante_id', 'tramites_servicio_social.institucion_receptora as descripcion', 'estudiantes.nombre_completo', 'estudiantes.matricula', 'tramites_servicio_social.estado')->orderByDesc('tramites_servicio_social.created_at')->get(),
                'actividades' => DB::table('tramites_actividad_complementaria')->join('estudiantes', 'estudiantes.id', '=', 'tramites_actividad_complementaria.estudiante_id')->select('tramites_actividad_complementaria.id', 'tramites_actividad_complementaria.estudiante_id', 'tramites_actividad_complementaria.nombre_actividad as descripcion', 'estudiantes.nombre_completo', 'estudiantes.matricula')->orderByDesc('tramites_actividad_complementaria.created_at')->get(),
            ],
            'personal' => DB::table('personal')->join('personal_rol', 'personal_rol.personal_id', '=', 'personal.id')->join('roles_documento', 'roles_documento.id', '=', 'personal_rol.rol_id')->where('personal.activo', true)->where('personal_rol.activo', true)->select('personal.id', 'personal.nombre_completo', 'personal.puesto_actual', 'roles_documento.id as rol_id')->orderBy('personal.nombre_completo')->get()->groupBy('rol_id'),
            'bloques' => DB::table('bloques_firma')->join('tipos_documento', 'tipos_documento.id', '=', 'bloques_firma.tipo_documento_id')->join('roles_documento', 'roles_documento.id', '=', 'bloques_firma.rol_id')->select('bloques_firma.*', 'tipos_documento.clave as tipo_clave', 'roles_documento.nombre_visible as rol')->orderBy('bloques_firma.orden')->get(),
        ]);
    }

    public function store(Request $request, DocumentoWord $word): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documento', 'id')->where('activo', true)],
            'estudiante_id' => ['required', 'integer', Rule::exists('estudiantes', 'id')],
            'tramite_type' => ['required', Rule::in(['tramites_residencia', 'tramites_servicio_social', 'tramites_actividad_complementaria'])],
            'tramite_id' => ['required', 'integer'],
            'destinatario' => ['required', 'string', 'max:200'],
            'firmantes' => ['required', 'array'],
            'firmantes.*' => ['required', 'integer', Rule::exists('personal', 'id')->where('activo', true)],
        ]);

        $tipo = DB::table('tipos_documento')->where('id', $datos['tipo_documento_id'])->where('activo', true)->first();
        $tramite = DB::table($datos['tramite_type'])->where('id', $datos['tramite_id'])->where('estudiante_id', $datos['estudiante_id'])->first();
        abort_unless($tramite, 422, 'El trámite seleccionado no corresponde al estudiante.');

        $esperados = match ($tipo->clave) {
            'RES_ACEP', 'RES_TERM' => 'tramites_residencia',
            'SS_ACEP', 'SS_TERM' => 'tramites_servicio_social',
            'CTA' => 'tramites_actividad_complementaria',
            default => null,
        };
        abort_unless($esperados === $datos['tramite_type'], 422, 'El tipo de documento no corresponde al trámite.');

        $bloques = DB::table('bloques_firma')->where('tipo_documento_id', $tipo->id)->orderBy('orden')->get();
        foreach ($bloques as $bloque) {
            $personaId = $datos['firmantes'][$bloque->id] ?? null;
            if ($bloque->obligatorio && ! $personaId) {
                return back()->withInput()->withErrors(['firmantes' => 'Falta asignar: '.$bloque->etiqueta]);
            }
            if ($personaId) {
                $tieneRol = DB::table('personal_rol')->where('personal_id', $personaId)->where('rol_id', $bloque->rol_id)->where('activo', true)->exists();
                if (! $tieneRol) {
                    return back()->withInput()->withErrors(['firmantes' => 'El personal seleccionado no tiene el cargo requerido: '.$bloque->etiqueta]);
                }
            }
        }

        $estudiante = DB::table('estudiantes')->where('id', $datos['estudiante_id'])->first();
        $anio = (int) now()->format('Y');
        $id = DB::transaction(function () use ($datos, $tipo, $tramite, $estudiante, $bloques, $word, $anio, $request): int {
            DB::table('folios_contador')->insertOrIgnore(['tipo_documento_id' => $tipo->id, 'anio' => $anio, 'ultimo_numero' => 0]);
            $contador = DB::table('folios_contador')->where('tipo_documento_id', $tipo->id)->where('anio', $anio)->lockForUpdate()->first();
            $numero = $contador->ultimo_numero + 1;
            DB::table('folios_contador')->where('tipo_documento_id', $tipo->id)->where('anio', $anio)->update(['ultimo_numero' => $numero]);
            $folio = sprintf('%s-%d-%06d', $tipo->prefijo_folio, $anio, $numero);
            $nombre = $folio.'.docx';
            $ruta = 'documentos/'.$nombre;
            $path = Storage::disk('local')->path($ruta);
            Storage::disk('local')->makeDirectory('documentos');
            $personas = DB::table('personal')->whereIn('id', array_filter($datos['firmantes']))->get()->keyBy('id');
            $parrafos = $this->contenido($tipo, $tramite, $estudiante, $datos['destinatario'], $folio, $personas, $bloques, $datos['firmantes']);
            $word->crear($parrafos, $path);
            $hash = hash_file('sha256', $path);
            $documentoId = DB::table('documentos_generados')->insertGetId([
                'folio' => $folio,
                'tipo_documento_id' => $tipo->id,
                'estudiante_id' => $estudiante->id,
                'tramite_type' => $datos['tramite_type'],
                'tramite_id' => $tramite->id,
                'version_plantilla_usada' => $tipo->version_plantilla,
                'fecha_generacion' => now(),
                'usuario_id' => $request->user()->id,
                'nombre_archivo' => $nombre,
                'ruta_archivo' => $ruta,
                'hash_archivo' => $hash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($bloques as $bloque) {
                $personaId = $datos['firmantes'][$bloque->id] ?? null;
                if (! $personaId) {
                    continue;
                }
                $persona = $personas->get($personaId);
                DB::table('documento_firmante')->insert([
                    'documento_generado_id' => $documentoId,
                    'bloque_firma_id' => $bloque->id,
                    'personal_id' => $personaId,
                    'nombre_snapshot' => $persona->nombre_completo,
                    'puesto_snapshot' => $persona->puesto_actual ?: $bloque->etiqueta,
                ]);
            }

            DB::table('activity_log')->insert([
                'log_name' => 'documentos',
                'description' => 'Documento generado: '.$folio,
                'subject_type' => 'documentos_generados',
                'event' => 'created',
                'subject_id' => $documentoId,
                'causer_type' => User::class,
                'causer_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $documentoId;
        });

        return redirect()->route('documentos.index')->with('status', 'Documento generado y archivado. Descárgalo para revisar su contenido.');
    }

    public function download(int $documento): StreamedResponse
    {
        $registro = DB::table('documentos_generados')->where('id', $documento)->first();
        abort_unless($registro && Storage::disk('local')->exists($registro->ruta_archivo), 404);
        abort_unless(hash_file('sha256', Storage::disk('local')->path($registro->ruta_archivo)) === $registro->hash_archivo, 409, 'El archivo no coincide con la huella registrada.');

        return Storage::disk('local')->download($registro->ruta_archivo, basename($registro->nombre_archivo));
    }

    private function contenido(object $tipo, object $tramite, object $estudiante, string $destinatario, string $folio, $personas, $bloques, array $firmantes): array
    {
        $contenido = [
            'INSTITUTO TECNOLÓGICO SUPERIOR DEL ORIENTE DEL ESTADO DE HIDALGO',
            'DIVISIÓN DE INGENIERÍA EN SISTEMAS COMPUTACIONALES',
            $tipo->nombre,
            'Folio: '.$folio,
            '',
            'A quien corresponda:',
            'Destinatario: '.$destinatario,
            'Por medio de la presente se hace constar la información correspondiente al trámite registrado para:',
            'Estudiante: '.$estudiante->nombre_completo,
            'Matrícula: '.$estudiante->matricula,
        ];

        foreach ($tramite as $campo => $valor) {
            if (in_array($campo, ['id', 'estudiante_id', 'created_at', 'updated_at'], true) || $valor === null) {
                continue;
            }
            $etiqueta = ucfirst(str_replace('_', ' ', $campo));
            $contenido[] = $etiqueta.': '.$valor;
        }

        $contenido[] = '';
        $contenido[] = 'Documento generado el '.now()->format('d/m/Y').'. Verifique los datos y el formato institucional antes de su emisión oficial.';
        $contenido[] = '';
        foreach ($bloques as $bloque) {
            $persona = $personas->get($firmantes[$bloque->id] ?? 0);
            if ($persona) {
                $contenido[] = '_______________________________';
                $contenido[] = $persona->nombre_completo;
                $contenido[] = $persona->puesto_actual ?: $bloque->etiqueta;
            }
        }

        return $contenido;
    }
}
