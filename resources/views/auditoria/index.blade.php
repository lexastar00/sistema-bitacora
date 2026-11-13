@extends('layouts.app')
@section('title', 'Auditoría')
@section('content')
    <h1>Auditoría</h1><p class="sub">Actividad registrada de las cuentas y módulos.</p>
    <section class="card">
        @if($eventos->isEmpty())<p class="empty">Todavía no hay eventos registrados.</p>
        @else<div class="tablewrap"><table><thead><tr><th>Fecha</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Registro</th></tr></thead><tbody>
            @foreach($eventos as $evento)<tr>
                <td>{{ \Illuminate\Support\Carbon::parse($evento->created_at)->format('d/m/Y H:i:s') }}</td>
                <td>{{ $evento->usuario ?: 'Cuenta eliminada' }}</td>
                <td>{{ $evento->log_name ?: 'General' }}</td>
                <td>{{ $evento->description }}</td>
                <td>{{ $evento->subject_type }}{{ $evento->subject_id ? ' #'.$evento->subject_id : '' }}</td>
            </tr>@endforeach
        </tbody></table></div>{{ $eventos->links() }}@endif
    </section>
@endsection
