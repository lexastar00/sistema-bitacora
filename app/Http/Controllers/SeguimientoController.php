<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SeguimientoController extends Controller
{
    public function __invoke(): View
    {
        return view('seguimiento.index', [
            'residencias' => DB::table('tramites_residencia')
                ->join('estudiantes', 'estudiantes.id', '=', 'tramites_residencia.estudiante_id')
                ->where('tramites_residencia.estado', 'en_curso')
                ->select('estudiantes.id as estudiante_id', 'estudiantes.nombre_completo', 'estudiantes.matricula', 'tramites_residencia.proyecto as tramite', 'tramites_residencia.empresa_institucion as lugar', 'tramites_residencia.fecha_termino')
                ->orderBy('tramites_residencia.fecha_termino')
                ->get(),
            'servicioSocial' => DB::table('tramites_servicio_social')
                ->join('estudiantes', 'estudiantes.id', '=', 'tramites_servicio_social.estudiante_id')
                ->where('tramites_servicio_social.estado', 'en_curso')
                ->select('estudiantes.id as estudiante_id', 'estudiantes.nombre_completo', 'estudiantes.matricula', 'tramites_servicio_social.institucion_receptora as lugar', 'tramites_servicio_social.fecha_termino')
                ->orderBy('tramites_servicio_social.fecha_termino')
                ->get(),
        ]);
    }
}
