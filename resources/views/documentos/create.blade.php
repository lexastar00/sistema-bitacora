@extends('layouts.app')
@section('title', 'Generar documento')
@section('content')
    <h1>Generar documento Word</h1><p class="sub">El sistema asigna folio, archiva el archivo y conserva la huella SHA-256.</p>
    <div class="errors" role="note">Nota: se genera un borrador DOCX con los datos del sistema; revisa el contenido y aplícale el formato institucional vigente antes de emitirlo oficialmente.</div>
    <form class="card" method="POST" action="{{ route('documentos.store') }}">@csrf
        <div class="formgrid">
            <div class="field"><label for="tipo_documento_id">Tipo de documento</label><select id="tipo_documento_id" name="tipo_documento_id" required><option value="">Selecciona el documento</option>@foreach($tipos as $tipo)<option value="{{ $tipo->id }}" data-clave="{{ $tipo->clave }}" @selected((string)old('tipo_documento_id')===(string)$tipo->id)>{{ $tipo->nombre }}</option>@endforeach</select></div>
            <div class="field"><label for="estudiante_id">Estudiante</label><select id="estudiante_id" name="estudiante_id" required><option value="">Selecciona el estudiante</option>@foreach($estudiantes as $e)<option value="{{ $e->id }}" @selected((string)old('estudiante_id')===(string)$e->id)>{{ $e->nombre_completo }} · {{ $e->matricula }}</option>@endforeach</select></div>
            <div class="field"><label for="tramite_id">Trámite asociado</label><select id="tramite_id" name="tramite_id" required><option value="">Selecciona el trámite</option>
                @foreach($tramites['residencias'] as $t)<option value="{{ $t->id }}" data-estudiante="{{ $t->estudiante_id }}" data-tipo="residencia" data-table="tramites_residencia">{{ $t->nombre_completo }} · Residencia: {{ $t->descripcion }} ({{ $t->estado }})</option>@endforeach
                @foreach($tramites['servicioSocial'] as $t)<option value="{{ $t->id }}" data-estudiante="{{ $t->estudiante_id }}" data-tipo="servicio" data-table="tramites_servicio_social">{{ $t->nombre_completo }} · Servicio social: {{ $t->descripcion }} ({{ $t->estado }})</option>@endforeach
                @foreach($tramites['actividades'] as $t)<option value="{{ $t->id }}" data-estudiante="{{ $t->estudiante_id }}" data-tipo="actividad" data-table="tramites_actividad_complementaria">{{ $t->nombre_completo }} · Actividad: {{ $t->descripcion }}</option>@endforeach
            </select></div>
            <div class="field"><label for="destinatario">Destinatario de la carta/constancia</label><input id="destinatario" name="destinatario" value="{{ old('destinatario') }}" maxlength="200" required></div>
            <input id="tramite_type" type="hidden" name="tramite_type" value="{{ old('tramite_type') }}">
        </div>
        <section style="margin-top:24px"><h2>Firmantes</h2><p class="hint">Solo aparecen personas a quienes se les asignó el cargo correspondiente en la sección Personal.</p>
            @foreach($bloques as $bloque)<div class="field signer" data-tipo="{{ $bloque->tipo_clave }}" style="margin-bottom:14px">
                <label for="signer-{{ $bloque->id }}">{{ $bloque->etiqueta }} @if($bloque->obligatorio)<span class="hint">(obligatorio)</span>@endif</label>
                <select id="signer-{{ $bloque->id }}" name="firmantes[{{ $bloque->id }}]" @required($bloque->obligatorio) data-role="{{ $bloque->rol_id }}">
                    <option value="">Selecciona</option>
                    @foreach(($personal->get($bloque->rol_id) ?? collect()) as $persona)
                        <option value="{{ $persona->id }}" @selected((string)old("firmantes.{$bloque->id}")===(string)$persona->id)>{{ $persona->nombre_completo }}{{ $persona->puesto_actual ? ' — '.$persona->puesto_actual : '' }}</option>
                    @endforeach
                </select>
            </div>@endforeach
        </section>
        <button class="btn" style="margin-top:16px">Generar y archivar DOCX</button>
    </form>
    <script>
        const typeSelect = document.querySelector('#tipo_documento_id');
        const studentSelect = document.querySelector('#estudiante_id');
        const processSelect = document.querySelector('#tramite_id');
        const typeMap = {RES_ACEP:'residencia',RES_TERM:'residencia',SS_ACEP:'servicio',SS_TERM:'servicio',CTA:'actividad'};
        function updateOptions() {
            const clave = typeSelect.selectedOptions[0]?.dataset.clave || '';
            const processType = typeMap[clave] || '';
            const student = studentSelect.value;
            let selectedAvailable = false;
            [...processSelect.options].forEach((option, index) => {
                if (index === 0) return;
                const visible = option.dataset.tipo === processType && option.dataset.estudiante === student;
                option.hidden = !visible;
                if (visible && option.selected) selectedAvailable = true;
            });
            if (!selectedAvailable) processSelect.value = '';
            document.querySelector('#tramite_type').value = processSelect.selectedOptions[0]?.dataset.table || '';
            document.querySelectorAll('.signer').forEach(element => {
                const visible = element.dataset.tipo === clave;
                element.hidden = !visible;
                const select = element.querySelector('select');
                select.required = visible && select.hasAttribute('data-role');
            });
        }
        typeSelect.addEventListener('change', updateOptions);
        studentSelect.addEventListener('change', updateOptions);
        processSelect.addEventListener('change', () => document.querySelector('#tramite_type').value = processSelect.selectedOptions[0]?.dataset.table || '');
        updateOptions();
    </script>
@endsection
