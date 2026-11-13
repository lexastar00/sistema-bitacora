@extends('layouts.app')

@section('title', 'Atenciones')

@section('content')
    <div class="row">
        <div>
            <h1>Bitácora de atenciones</h1>
            <p class="sub">Consulta los registros del Área de Sistemas.</p>
        </div>
        @can('bitacora.crear')
            <a class="btn" href="{{ route('atenciones.create') }}">Registrar atención</a>
        @endcan
    </div>

    <section class="card">
        <form method="GET" action="{{ route('atenciones.index') }}" class="formgrid" style="margin-bottom:20px">
            <div class="field">
                <label for="q">Buscar</label>
                <input id="q" name="q" value="{{ request('q') }}" placeholder="Folio, nombre, matrícula o asunto">
            </div>
            <div class="field">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" @selected((string) request('estado') === (string) $estado->id)>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="desde">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ request('desde') }}">
            </div>
            <div class="field">
                <label for="hasta">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ request('hasta') }}">
            </div>
            <div class="field" style="justify-content:flex-end;flex-direction:row;align-items:end">
                <button class="btn" type="submit">Aplicar filtros</button>
                <a class="btn secondary" href="{{ route('atenciones.index') }}">Limpiar</a>
            </div>
        </form>
        @if ($atenciones->isEmpty())
            <p class="empty">No hay atenciones que coincidan con la búsqueda.</p>
        @else
            <div class="tablewrap">
                <table>
                    <thead><tr><th>Folio</th><th>Fecha</th><th>Solicitante</th><th>Asunto</th><th>Servicio</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($atenciones as $atencion)
                        <tr>
                            <td>{{ $atencion->folio }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($atencion->fecha_hora)->format('d/m/Y H:i') }}</td>
                            <td>{{ $atencion->solicitante_nombre }}</td>
                            <td>{{ $atencion->asunto }}</td>
                            <td>{{ $atencion->servicio }}</td>
                            <td><span class="badge">{{ $atencion->estado }}</span></td>
                            <td><a href="{{ route('atenciones.show', $atencion->id) }}">Ver detalle</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $atenciones->links() }}</div>
        @endif
    </section>
@endsection
