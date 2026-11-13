@extends('layouts.app')

@section('title', 'Registrar atención')

@section('content')
    <div class="row">
        <div>
            <h1>Registrar atención</h1>
            <p class="sub">Completa los datos para agregar una entrada a la bitácora.</p>
        </div>
        <a class="btn secondary" href="{{ route('atenciones.index') }}">Volver a bitácora</a>
    </div>

    @if ($sinPersonal)
        <div class="card">
            <p>Aún no hay personal activo para asignar los responsables requeridos.</p>
            @can('catalogos.administrar')
                <a class="btn" href="{{ route('personal.index') }}">Agregar personal</a>
            @endcan
        </div>
    @elseif ($servicios->isEmpty() || $tiposAtencion->isEmpty())
        <div class="card">
            <p>Faltan servicios o tipos de atención activos. Solicita al administrador que complete los catálogos.</p>
        </div>
    @else
        <form class="card" method="POST" action="{{ route('atenciones.store') }}">
            @csrf
            <div class="formgrid">
                <div class="field">
                    <label for="tipo_solicitante">Tipo de solicitante</label>
                    <select id="tipo_solicitante" name="tipo_solicitante" required>
                        @foreach (['alumno' => 'Alumno', 'docente' => 'Docente', 'administrativo' => 'Administrativo', 'otro' => 'Otro'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('tipo_solicitante', 'alumno') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="solicitante_nombre">Nombre del solicitante</label>
                    <input id="solicitante_nombre" name="solicitante_nombre" value="{{ old('solicitante_nombre') }}" maxlength="200" required>
                </div>
                <div class="field">
                    <label for="matricula">Matrícula <span class="hint">(si aplica)</span></label>
                    <input id="matricula" name="matricula" value="{{ old('matricula') }}" maxlength="20">
                </div>
                <div class="field">
                    <label for="asunto">Asunto</label>
                    <input id="asunto" name="asunto" value="{{ old('asunto') }}" maxlength="255" required>
                </div>
                <div class="field">
                    <label for="servicio_id">Servicio</label>
                    <select id="servicio_id" name="servicio_id" required>
                        <option value="">Selecciona un servicio</option>
                        @foreach ($servicios as $servicio)
                            <option value="{{ $servicio->id }}" @selected((string) old('servicio_id') === (string) $servicio->id)>{{ $servicio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="tipo_atencion_id">Tipo de atención</label>
                    <select id="tipo_atencion_id" name="tipo_atencion_id" required>
                        <option value="">Selecciona un tipo</option>
                        @foreach ($tiposAtencion as $tipo)
                            <option value="{{ $tipo->id }}" @selected((string) old('tipo_atencion_id') === (string) $tipo->id)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="tipo_problema_id">Tipo de problema <span class="hint">(opcional)</span></label>
                    <select id="tipo_problema_id" name="tipo_problema_id">
                        <option value="">Sin especificar</option>
                        @foreach ($tiposProblema as $tipo)
                            <option value="{{ $tipo->id }}" @selected((string) old('tipo_problema_id') === (string) $tipo->id)>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="jefe_id">Jefe responsable</label>
                    <select id="jefe_id" name="jefe_id" required>
                        <option value="">Selecciona al responsable</option>
                        @foreach ($personal as $persona)
                            <option value="{{ $persona->id }}" @selected((string) old('jefe_id') === (string) $persona->id)>{{ $persona->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="secretario_id">Secretario responsable</label>
                    <select id="secretario_id" name="secretario_id" required>
                        <option value="">Selecciona al responsable</option>
                        @foreach ($personal as $persona)
                            <option value="{{ $persona->id }}" @selected((string) old('secretario_id') === (string) $persona->id)>{{ $persona->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field full">
                    <label for="descripcion_problema">Descripción de la solicitud o problema</label>
                    <textarea id="descripcion_problema" name="descripcion_problema" maxlength="10000" required>{{ old('descripcion_problema') }}</textarea>
                </div>
                <div class="field full">
                    <label for="observaciones">Observaciones <span class="hint">(opcional)</span></label>
                    <textarea id="observaciones" name="observaciones" maxlength="10000">{{ old('observaciones') }}</textarea>
                </div>
            </div>
            <div class="row" style="justify-content:flex-end;margin-top:22px;margin-bottom:0">
                <button class="btn" type="submit">Guardar atención</button>
            </div>
        </form>
    @endif
@endsection
