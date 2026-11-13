@extends('layouts.app')

@section('title', $atencion->folio)

@section('content')
    <div class="row">
        <div>
            <h1>{{ $atencion->folio }}</h1>
            <p class="sub">{{ $atencion->asunto }}</p>
        </div>
        <a class="btn secondary" href="{{ route('atenciones.index') }}">Volver a bitácora</a>
    </div>

    <section class="card" style="margin-bottom:20px">
        <div class="grid" style="margin-top:0">
            <div><small>Solicitante</small><p><strong>{{ $atencion->solicitante_nombre }}</strong><br>{{ ucfirst($atencion->tipo_solicitante) }} · {{ $atencion->matricula ?: 'Sin matrícula' }}</p></div>
            <div><small>Fecha y servicio</small><p><strong>{{ \Illuminate\Support\Carbon::parse($atencion->fecha_hora)->format('d/m/Y H:i') }}</strong><br>{{ $atencion->servicio }}</p></div>
            <div><small>Estado</small><p><span class="badge">{{ $atencion->estado }}</span></p></div>
        </div>
        <h2>Descripción</h2>
        <p>{{ $atencion->descripcion_problema }}</p>
        @if ($atencion->solucion)<h2>Solución</h2><p>{{ $atencion->solucion }}</p>@endif
        @if ($atencion->observaciones)<h2>Observaciones</h2><p>{{ $atencion->observaciones }}</p>@endif
        <p class="hint">Registró {{ $atencion->usuario_registro }} · {{ $atencion->hora_salida ? 'Salida '.\Illuminate\Support\Carbon::parse($atencion->hora_salida)->format('d/m/Y H:i') : 'Sin hora de salida' }}</p>
    </section>

    <section class="card" style="margin-bottom:20px">
        <h2>Agregar seguimiento</h2>
        <form method="POST" action="{{ route('atenciones.update', $atencion->id) }}">
            @csrf @method('PUT')
            <div class="formgrid">
                @can('bitacora.cambiar_estado')
                    <div class="field">
                        <label for="estado_id">Cambiar estado</label>
                        <select id="estado_id" name="estado_id">
                            <option value="">Sin cambio</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}" @selected($atencion->estado_id === $estado->id)>{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endcan
                @can('bitacora.reasignar')
                    <div class="field">
                        <label for="atendido_por_id">Asignar a</label>
                        <select id="atendido_por_id" name="atendido_por_id">
                            <option value="">Sin cambio</option>
                            @foreach ($personal as $persona)
                                <option value="{{ $persona->id }}" @selected($atencion->atendido_por_id === $persona->id)>{{ $persona->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                @endcan
                <div class="field full">
                    <label for="comentario">Comentario</label>
                    <textarea id="comentario" name="comentario" maxlength="5000"></textarea>
                </div>
            </div>
            <button class="btn" type="submit" style="margin-top:16px">Guardar seguimiento</button>
        </form>
    </section>

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));align-items:start">
        <section class="card">
            <h2>Historial</h2>
            @forelse ($seguimientos as $item)
                <div style="border-left:3px solid #1769aa;padding:0 0 16px 14px;margin:12px 0">
                    <strong>{{ $item->estado_nuevo }}</strong>
                    @if ($item->responsable_nuevo_id)<span class="hint"> · responsable asignado</span>@endif
                    <div class="hint">{{ $item->usuario }} · {{ \Illuminate\Support\Carbon::parse($item->fecha)->format('d/m/Y H:i') }}</div>
                    @if ($item->comentario)<p style="margin:8px 0">{{ $item->comentario }}</p>@endif
                </div>
            @empty
                <p class="empty">No hay movimientos de seguimiento.</p>
            @endforelse
        </section>
        <section class="card">
            <h2>Evidencias</h2>
            @can('bitacora.adjuntar_evidencia')
                <form method="POST" action="{{ route('atenciones.evidencias.upload', $atencion->id) }}" enctype="multipart/form-data" style="margin-bottom:18px">
                    @csrf
                    <div class="field"><label for="archivo">Adjuntar PDF o imagen (máx. 10 MB)</label><input id="archivo" type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <button class="btn" type="submit" style="margin-top:12px">Subir evidencia</button>
                </form>
            @endcan
            @forelse ($adjuntos as $adjunto)
                <p><a href="{{ route('atenciones.evidencias.download', [$atencion->id, $adjunto->id]) }}">{{ $adjunto->nombre_archivo }}</a> <span class="hint">({{ $adjunto->tamano_kb }} KB)</span></p>
            @empty
                <p class="empty">No hay evidencias adjuntas.</p>
            @endforelse
        </section>
    </div>
@endsection
