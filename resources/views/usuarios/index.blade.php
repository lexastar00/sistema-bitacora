@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
    <h1>Administración de usuarios</h1><p class="sub">Crea cuentas, asigna roles y desactiva accesos.</p>
    <section class="card" style="margin-bottom:20px"><h2>Crear usuario</h2>
        <form method="POST" action="{{ route('usuarios.store') }}">@csrf
            <div class="formgrid">
                <div class="field"><label>Nombre</label><input name="name" value="{{ old('name') }}" required maxlength="255"></div>
                <div class="field"><label>Correo</label><input type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="field"><label>Contraseña (mínimo 12 caracteres)</label><input type="password" name="password" required minlength="12"></div>
                <div class="field"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" required minlength="12"></div>
                <div class="field"><label>Rol</label><select name="role" required>@foreach($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select></div>
                <div class="field"><label>Vincular personal (opcional)</label><select name="personal_id"><option value="">Sin vincular</option>@foreach($personal as $p)<option value="{{ $p->id }}">{{ $p->nombre_completo }}</option>@endforeach</select></div>
            </div><button class="btn" style="margin-top:18px">Crear cuenta</button>
        </form>
    </section>
    <section class="card"><h2>Cuentas existentes</h2>
        <div class="tablewrap"><table><thead><tr><th>Nombre y correo</th><th>Rol</th><th>Vincular personal</th><th>Acceso</th><th>Guardar</th></tr></thead><tbody>
        @foreach($usuarios as $usuario)<tr>
            <td><form id="usuario-{{ $usuario->id }}" method="POST" action="{{ route('usuarios.update',$usuario->id) }}">@csrf @method('PUT')</form><input form="usuario-{{ $usuario->id }}" name="name" value="{{ $usuario->name }}" required><input form="usuario-{{ $usuario->id }}" type="email" name="email" value="{{ $usuario->email }}" required style="margin-top:6px"></td>
            <td><select form="usuario-{{ $usuario->id }}" name="role" required>@foreach($roles as $role)<option value="{{ $role->name }}" @selected($usuario->hasRole($role->name))>{{ $role->name }}</option>@endforeach</select></td>
            <td><select form="usuario-{{ $usuario->id }}" name="personal_id"><option value="">Sin vincular</option>@foreach($personal as $p)<option value="{{ $p->id }}" @selected($usuario->personal_id === $p->id)>{{ $p->nombre_completo }}</option>@endforeach</select></td>
            <td><select form="usuario-{{ $usuario->id }}" name="activo"><option value="1" @selected($usuario->activo)>Activo</option><option value="0" @selected(!$usuario->activo)>Desactivado</option></select></td>
            <td><input form="usuario-{{ $usuario->id }}" type="password" name="password" placeholder="Nueva contraseña (opcional)" minlength="12"><input form="usuario-{{ $usuario->id }}" type="password" name="password_confirmation" placeholder="Confirmar contraseña" minlength="12"><button form="usuario-{{ $usuario->id }}" class="btn" style="margin-top:6px">Guardar</button></td>
        </tr>@endforeach
        </tbody></table></div>{{ $usuarios->links() }}
    </section>
@endsection
