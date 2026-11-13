@extends('layouts.app')

@section('title', 'Panel principal')

@section('content')
    <h1>Panel principal</h1>
    <p class="sub">Resumen de la actividad del Área de Sistemas.</p>

    <section class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr))" aria-label="Resumen">
        <article class="card metric"><small>Atenciones de hoy</small><strong>{{ $atencionesHoy }}</strong></article>
        <article class="card metric"><small>Alumnos atendidos hoy</small><strong>{{ $alumnosHoy }}</strong></article>
        <article class="card metric"><small>Docentes atendidos hoy</small><strong>{{ $docentesHoy }}</strong></article>
        <article class="card metric"><small>Atenciones pendientes</small><strong>{{ $pendientes }}</strong></article>
        <article class="card metric"><small>Atenciones resueltas</small><strong>{{ $resueltas }}</strong></article>
    </section>

    <section class="card">
        <div class="row">
            <h2>Últimas atenciones</h2>
            @can('bitacora.crear')
                <a class="btn" href="{{ route('atenciones.create') }}">Registrar atención</a>
            @endcan
        </div>
        @if ($ultimasAtenciones->isEmpty())
            <p class="empty">Todavía no hay atenciones registradas.</p>
        @else
            <div class="tablewrap">
                <table>
                    <thead><tr><th>Folio</th><th>Fecha</th><th>Solicitante</th><th>Asunto</th><th>Estado</th></tr></thead>
                    <tbody>
                    @foreach ($ultimasAtenciones as $atencion)
                        <tr>
                            <td>{{ $atencion->folio }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($atencion->fecha_hora)->format('d/m/Y H:i') }}</td>
                            <td>{{ $atencion->solicitante_nombre }}</td>
                            <td>{{ $atencion->asunto }}</td>
                            <td><span class="badge">{{ $atencion->estado }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
