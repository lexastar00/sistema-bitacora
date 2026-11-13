@extends('layouts.app')
@section('title', 'Registrar estudiante')
@section('content')
    <h1>Registrar estudiante</h1><p class="sub">Captura los datos permanentes del estudiante.</p>
    <form class="card" method="POST" action="{{ route('estudiantes.store') }}">@csrf
        <div class="formgrid">
            <div class="field"><label for="nombre_completo">Nombre completo</label><input id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" required maxlength="200"></div>
            <div class="field"><label for="matricula">Matrícula</label><input id="matricula" name="matricula" value="{{ old('matricula') }}" required maxlength="20"></div>
            <div class="field"><label for="carrera_id">Carrera</label><select id="carrera_id" name="carrera_id" required>@foreach($carreras as $carrera)<option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>@endforeach</select></div>
            <div class="field"><label for="semestre">Semestre</label><input id="semestre" name="semestre" type="number" min="1" max="16" value="{{ old('semestre') }}"></div>
            <div class="field"><label for="correo_institucional">Correo institucional</label><input id="correo_institucional" name="correo_institucional" type="email" value="{{ old('correo_institucional') }}"></div>
            <div class="field"><label for="telefono">Teléfono</label><input id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="20"></div>
            <div class="field"><label for="periodo_id">Periodo actual</label><select id="periodo_id" name="periodo_id"><option value="">Sin asignar</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>@endforeach</select></div>
            <div class="field"><label for="estatus">Estatus</label><select id="estatus" name="estatus"><option value="activo">Activo</option><option value="egresado">Egresado</option><option value="baja">Baja</option></select></div>
        </div><button class="btn" type="submit" style="margin-top:20px">Guardar estudiante</button>
    </form>
@endsection
