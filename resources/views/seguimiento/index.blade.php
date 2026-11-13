@extends('layouts.app')
@section('title', 'Seguimiento estudiantil')
@section('content')
    <h1>Seguimiento estudiantil</h1><p class="sub">Residencias y servicio social que continúan en curso.</p>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));align-items:start">
        <section class="card"><h2>Residencias en curso</h2>
            @forelse($residencias as $t)<p><a href="{{ route('estudiantes.show',$t->estudiante_id) }}"><strong>{{ $t->nombre_completo }}</strong> · {{ $t->matricula }}</a><br>{{ $t->tramite }} — {{ $t->lugar }}<br><span class="hint">Finaliza: {{ $t->fecha_termino }}</span></p>@empty<p class="empty">No hay residencias activas.</p>@endforelse
        </section>
        <section class="card"><h2>Servicio social en curso</h2>
            @forelse($servicioSocial as $t)<p><a href="{{ route('estudiantes.show',$t->estudiante_id) }}"><strong>{{ $t->nombre_completo }}</strong> · {{ $t->matricula }}</a><br>{{ $t->lugar }}<br><span class="hint">Finaliza: {{ $t->fecha_termino }}</span></p>@empty<p class="empty">No hay servicios sociales activos.</p>@endforelse
        </section>
    </div>
@endsection
