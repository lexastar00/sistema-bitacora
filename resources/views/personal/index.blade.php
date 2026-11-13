@extends('layouts.app')

@section('title', 'Personal')

@section('content')
    <h1>Personal</h1>
    <p class="sub">Administra las personas que pueden asignarse como responsables de una atención.</p>

    <section class="card" style="margin-bottom:20px">
        <h2>Agregar persona</h2>
        <form method="POST" action="{{ route('personal.store') }}">
            @csrf
            <div class="formgrid">
                <div class="field">
                    <label for="nombre_completo">Nombre completo</label>
                    <input id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" maxlength="200" required>
                </div>
                <div class="field">
                    <label for="puesto_actual">Puesto <span class="hint">(opcional)</span></label>
                    <input id="puesto_actual" name="puesto_actual" value="{{ old('puesto_actual') }}" maxlength="200">
                </div>
            </div>
            <button class="btn" type="submit" style="margin-top:18px">Agregar personal</button>
        </form>
    </section>

    <section class="card">
        <h2>Personal registrado</h2>
        @if ($personal->isEmpty())
            <p class="empty">Todavía no hay personal registrado.</p>
        @else
            <div class="tablewrap">
                <table>
                    <thead><tr><th>Nombre</th><th>Puesto</th><th>Cargos para documentos</th><th>Estado</th></tr></thead>
                    <tbody>
                    @foreach ($personal as $persona)
                        <tr>
                            <td>{{ $persona->nombre_completo }}</td>
                            <td>{{ $persona->puesto_actual ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('personal.roles', $persona->id) }}">@csrf
                                    <div style="display:grid;grid-template-columns:repeat(2,minmax(140px,1fr));gap:4px">
                                        @foreach($rolesDocumento as $rol)
                                            <label class="hint"><input type="checkbox" name="roles[]" value="{{ $rol->id }}" @checked(($asignaciones->get($persona->id, collect())->pluck('rol_id')->contains($rol->id)))> {{ $rol->nombre_visible }}</label>
                                        @endforeach
                                    </div>
                                    <button class="btn secondary" style="margin-top:8px">Guardar cargos</button>
                                </form>
                            </td>
                            <td><span class="badge">{{ $persona->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
