<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $usuario = auth()->user();
        $global = $usuario->can('dashboard.ver_global');

        if (! $global && ! $usuario->can('dashboard.ver_departamento')) {
            if ($usuario->can('bitacora.ver')) {
                return redirect()->route('atenciones.index');
            }

            abort(403);
        }

        $departamentoId = null;
        if (! $global) {
            $departamentoId = DB::table('personal')->where('id', $usuario->personal_id)->value('departamento_id');
            abort_if(! $departamentoId, 403, 'Tu cuenta no está vinculada a un departamento.');
        }

        $atencionesHoyQuery = DB::table('atenciones')->whereDate('fecha_hora', now()->toDateString());
        $pendientesQuery = DB::table('atenciones')
            ->join('estados_atencion', 'estados_atencion.id', '=', 'atenciones.estado_id')
            ->whereIn('estados_atencion.nombre', ['Registrada', 'En proceso', 'Pendiente', 'Reabierta']);
        $resueltasQuery = DB::table('atenciones')
            ->join('estados_atencion', 'estados_atencion.id', '=', 'atenciones.estado_id')
            ->where('estados_atencion.nombre', 'Resuelta');
        $alumnosQuery = DB::table('atenciones')->whereDate('fecha_hora', now()->toDateString())->where('tipo_solicitante', 'alumno');
        $docentesQuery = DB::table('atenciones')->whereDate('fecha_hora', now()->toDateString())->where('tipo_solicitante', 'docente');
        $ultimasQuery = DB::table('atenciones')
            ->join('estados_atencion', 'estados_atencion.id', '=', 'atenciones.estado_id')
            ->select('atenciones.folio', 'atenciones.fecha_hora', 'atenciones.solicitante_nombre', 'atenciones.asunto', 'estados_atencion.nombre as estado')
            ->latest('atenciones.fecha_hora')
            ->limit(5);

        if ($departamentoId) {
            $servicios = DB::table('servicios')->select('id')->where('departamento_id', $departamentoId);
            $atencionesHoyQuery->whereIn('servicio_id', $servicios);
            $pendientesQuery->whereIn('atenciones.servicio_id', $servicios);
            $resueltasQuery->whereIn('atenciones.servicio_id', $servicios);
            $alumnosQuery->whereIn('servicio_id', $servicios);
            $docentesQuery->whereIn('servicio_id', $servicios);
            $ultimasQuery->whereIn('atenciones.servicio_id', $servicios);
        }

        return view('dashboard', [
            'atencionesHoy' => $atencionesHoyQuery->count(),
            'pendientes' => $pendientesQuery->count(),
            'resueltas' => $resueltasQuery->count(),
            'alumnosHoy' => $alumnosQuery->count(),
            'docentesHoy' => $docentesQuery->count(),
            'ultimasAtenciones' => $ultimasQuery->get(),
        ]);
    }
}
