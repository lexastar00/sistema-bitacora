@extends('layouts.app')
@section('title', 'Documentos')
@section('content')
    <div class="row"><div><h1>Documentos generados</h1><p class="sub">Historial de folios y archivos oficiales generados.</p></div>@can('documentos.generar')<a class="btn" href="{{ route('documentos.create') }}">Generar documento</a>@endcan</div>
    <section class="card">
        @if($documentos->isEmpty())<p class="empty">Todavía no hay documentos generados.</p>
        @else<div class="tablewrap"><table><thead><tr><th>Folio</th><th>Documento</th><th>Estudiante</th><th>Fecha</th><th>Generó</th><th>Archivo</th></tr></thead><tbody>
            @foreach($documentos as $doc)<tr><td>{{ $doc->folio }}</td><td>{{ $doc->tipo }}</td><td>{{ $doc->estudiante }} · {{ $doc->matricula }}</td><td>{{ \Illuminate\Support\Carbon::parse($doc->fecha_generacion)->format('d/m/Y H:i') }}</td><td>{{ $doc->usuario }}</td><td><a href="{{ route('documentos.download',$doc->id) }}">Descargar Word</a></td></tr>@endforeach
        </tbody></table></div>{{ $documentos->links() }}@endif
    </section>
@endsection
