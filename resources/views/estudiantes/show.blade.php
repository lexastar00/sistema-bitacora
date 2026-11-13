@extends('layouts.app')
@section('title', $estudiante->nombre_completo)
@section('content')
    <div class="row"><div><h1>{{ $estudiante->nombre_completo }}</h1><p class="sub">{{ $estudiante->matricula }} · {{ $estudiante->carrera }}</p></div><a class="btn secondary" href="{{ route('estudiantes.index') }}">Volver a estudiantes</a></div>
    <section class="card" style="margin-bottom:20px">
        <div class="row"><h2>Datos del estudiante</h2><span class="badge">{{ ucfirst($estudiante->estatus) }}</span></div>
        <p>Semestre: {{ $estudiante->semestre ?: '—' }} · Periodo: {{ $estudiante->periodo ?: '—' }}</p>
        @if ($incluirContacto)<p>Correo: {{ $estudiante->correo_institucional ?: '—' }} · Teléfono: {{ $estudiante->telefono ?: '—' }}</p>@endif
        @can('estudiantes.editar')
            <details><summary>Editar datos</summary>
                <form method="POST" action="{{ route('estudiantes.update', $estudiante->id) }}" style="margin-top:16px">@csrf @method('PUT')
                    <div class="formgrid">
                        <div class="field"><label>Nombre completo</label><input name="nombre_completo" value="{{ $estudiante->nombre_completo }}" required maxlength="200"></div>
                        <div class="field"><label>Matrícula</label><input name="matricula" value="{{ $estudiante->matricula }}" required maxlength="20"></div>
                        <div class="field"><label>Carrera</label><select name="carrera_id" required>@foreach(DB::table('carreras')->where('activo', true)->get() as $carrera)<option value="{{ $carrera->id }}" @selected($estudiante->carrera_id === $carrera->id)>{{ $carrera->nombre }}</option>@endforeach</select></div>
                        <div class="field"><label>Semestre</label><input name="semestre" type="number" min="1" max="16" value="{{ $estudiante->semestre }}"></div>
                        @if ($incluirContacto)<div class="field"><label>Correo institucional</label><input name="correo_institucional" type="email" value="{{ $estudiante->correo_institucional }}"></div><div class="field"><label>Teléfono</label><input name="telefono" value="{{ $estudiante->telefono }}"></div>@endif
                        <div class="field"><label>Periodo</label><select name="periodo_id"><option value="">Sin asignar</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected($estudiante->periodo_id === $periodo->id)>{{ $periodo->nombre }}</option>@endforeach</select></div>
                        <div class="field"><label>Estatus</label><select name="estatus">@foreach(['activo','egresado','baja'] as $s)<option value="{{ $s }}" @selected($estudiante->estatus === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
                    </div><button class="btn" style="margin-top:16px">Actualizar</button>
                </form>
            </details>
        @endcan
    </section>

    @can('estudiantes.editar')
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));align-items:start">
        <section class="card"><h2>Nueva residencia profesional</h2>
            <form method="POST" action="{{ route('estudiantes.residencias.store', $estudiante->id) }}">@csrf
                <div class="field"><label>Proyecto</label><input name="proyecto" required maxlength="255"></div>
                <div class="field"><label>Empresa o institución</label><input name="empresa_institucion" required maxlength="255"></div>
                <div class="field"><label>Asesor interno</label><select name="asesor_interno_id"><option value="">Sin asignar</option>@foreach($personal as $persona)<option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>@endforeach</select></div>
                <div class="field"><label>Asesor externo</label><input name="asesor_externo" maxlength="200"></div>
                <div class="field"><label>Periodo escolar</label><select name="periodo_id"><option value="">Sin asignar</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>@endforeach</select></div>
                <div class="formgrid"><div class="field"><label>Inicio</label><input type="date" name="fecha_inicio" required></div><div class="field"><label>Término</label><input type="date" name="fecha_termino" required></div></div>
                <div class="field"><label>Horas</label><input type="number" name="horas" min="1" required></div>
                <input type="hidden" name="estado" value="en_curso"><button class="btn" style="margin-top:14px">Registrar residencia</button>
            </form>
        </section>
        <section class="card"><h2>Nuevo servicio social</h2>
            <form method="POST" action="{{ route('estudiantes.servicio-social.store', $estudiante->id) }}">@csrf
                <div class="field"><label>Institución receptora</label><input name="institucion_receptora" required maxlength="255"></div>
                <div class="field"><label>Programa o actividad</label><input name="programa_actividad" maxlength="255"></div>
                <div class="field"><label>Periodo escolar</label><select name="periodo_id"><option value="">Sin asignar</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>@endforeach</select></div>
                <div class="formgrid"><div class="field"><label>Inicio</label><input type="date" name="fecha_inicio" required></div><div class="field"><label>Término</label><input type="date" name="fecha_termino" required></div></div>
                <div class="field"><label>Horas</label><input type="number" name="horas" min="1" required></div>
                <input type="hidden" name="estado" value="en_curso"><button class="btn" style="margin-top:14px">Registrar servicio</button>
            </form>
        </section>
        <section class="card"><h2>Actividad complementaria</h2>
            <form method="POST" action="{{ route('estudiantes.actividades.store', $estudiante->id) }}">@csrf
                <div class="field"><label>Tipo de actividad</label><input name="tipo_actividad" required maxlength="100"></div>
                <div class="field"><label>Nombre de actividad</label><input name="nombre_actividad" required maxlength="255"></div>
                <div class="field"><label>Periodo escolar</label><input name="periodo_escolar" required maxlength="20"></div>
                <div class="field"><label>Créditos</label><input type="number" step="0.1" min="0.1" max="99.9" name="creditos" required></div>
                <button class="btn" style="margin-top:14px">Registrar actividad</button>
            </form>
        </section>
    </div>
    @endcan

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));align-items:start">
        <section class="card"><h2>Residencias</h2>@forelse($tramites['residencias'] as $t)<p><strong>{{ $t->proyecto }}</strong><br>{{ $t->empresa_institucion }} · {{ ucfirst(str_replace('_',' ',$t->estado)) }}<br><span class="hint">{{ $t->fecha_inicio }} - {{ $t->fecha_termino }} · {{ $t->horas }} h</span></p>@can('estudiantes.editar')<form method="POST" action="{{ route('estudiantes.residencias.update',[$estudiante->id,$t->id]) }}" class="row">@csrf @method('PUT')<select name="estado">@foreach(['en_curso','terminado','cancelado'] as $s)<option value="{{ $s }}" @selected($t->estado===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select><button class="btn secondary">Actualizar estado</button></form>@endcan @empty<p class="empty">Sin trámites.</p>@endforelse</section>
        <section class="card"><h2>Servicio social</h2>@forelse($tramites['servicioSocial'] as $t)<p><strong>{{ $t->institucion_receptora }}</strong><br>{{ ucfirst(str_replace('_',' ',$t->estado)) }}<br><span class="hint">{{ $t->fecha_inicio }} - {{ $t->fecha_termino }} · {{ $t->horas }} h</span></p>@can('estudiantes.editar')<form method="POST" action="{{ route('estudiantes.servicio-social.update',[$estudiante->id,$t->id]) }}" class="row">@csrf @method('PUT')<select name="estado">@foreach(['en_curso','terminado','cancelado'] as $s)<option value="{{ $s }}" @selected($t->estado===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select><button class="btn secondary">Actualizar estado</button></form>@endcan @empty<p class="empty">Sin trámites.</p>@endforelse</section>
        <section class="card"><h2>Actividades complementarias</h2>@forelse($tramites['actividades'] as $t)<p><strong>{{ $t->nombre_actividad }}</strong><br>{{ $t->tipo_actividad }} · {{ $t->periodo_escolar }} · {{ $t->creditos }} créditos</p>@empty<p class="empty">Sin actividades.</p>@endforelse</section>
    </div>
@endsection
