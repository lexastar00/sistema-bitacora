@extends('layouts.app')

@section('title', 'Estudiantes')

@section('content')
    <div class="row">
        <div><h1>Estudiantes</h1><p class="sub">Directorio y trámites académicos.</p></div>
        @can('estudiantes.crear')<a class="btn" href="{{ route('estudiantes.create') }}">Registrar estudiante</a>@endcan
    </div>
    <section class="card">
        <form method="GET" class="row" style="justify-content:flex-start">
            <input name="q" value="{{ request('q') }}" placeholder="Nombre o matrícula" style="max-width:380px">
            <select name="estatus" style="max-width:190px">
                <option value="">Todos los estatus</option>
                @foreach (['activo' => 'Activo', 'egresado' => 'Egresado', 'baja' => 'Baja'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('estatus') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Buscar</button>
        </form>
        @if ($estudiantes->isEmpty())
            <p class="empty">No hay estudiantes registrados.</p>
        @else
            <div class="tablewrap">
                <table>
                    <thead><tr><th>Matrícula</th><th>Nombre</th><th>Carrera</th><th>Semestre</th>@if ($incluirContacto)<th>Correo</th><th>Teléfono</th>@endif<th>Estatus</th><th></th></tr></thead>
                    <tbody>@foreach ($estudiantes as $estudiante)
                        <tr>
                            <td>{{ $estudiante->matricula }}</td><td>{{ $estudiante->nombre_completo }}</td><td>{{ $estudiante->carrera }}</td>
                            <td>{{ $estudiante->semestre ?: '—' }}</td>
                            @if ($incluirContacto)<td>{{ $estudiante->correo_institucional ?: '—' }}</td><td>{{ $estudiante->telefono ?: '—' }}</td>@endif
                            <td><span class="badge">{{ ucfirst($estudiante->estatus) }}</span></td>
                            <td><a href="{{ route('estudiantes.show', $estudiante->id) }}">Ver</a></td>
                        </tr>
                    @endforeach</tbody>
                </table>
            </div>
            {{ $estudiantes->links() }}
        @endif
    </section>
@endsection
