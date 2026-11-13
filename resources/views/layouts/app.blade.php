<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Bitácora') · TESI</title>
    <style>
        :root{font-family:Inter,Segoe UI,Arial,sans-serif;color:#172033;background:#f3f6fb;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0;min-height:100vh}a{color:inherit;text-decoration:none}
        .top{background:#102b4e;color:white}.nav{max-width:1180px;margin:auto;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:24px}
        .brand{font-weight:750;letter-spacing:.02em}.navlinks{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .navlinks a,.navlinks button{font:inherit;color:#dce9f8;background:transparent;border:0;padding:9px 12px;border-radius:8px;cursor:pointer}
        .navlinks a:hover,.navlinks a.active,.navlinks button:hover{background:#21466f;color:white}
        .who{color:#b8cce4;font-size:.88rem;margin-right:8px}.wrap{max-width:1180px;margin:0 auto;padding:32px 24px 60px}
        h1{font-size:clamp(1.7rem,3vw,2.25rem);margin:0 0 8px;color:#102b4e}h2{font-size:1.15rem;margin:0 0 16px}
        .sub{color:#607089;margin:0 0 26px}.card{background:white;border:1px solid #e1e8f0;border-radius:14px;padding:22px;box-shadow:0 8px 24px rgba(30,55,90,.045)}
        .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:22px 0}.metric small{display:block;color:#63738b;font-size:.9rem}.metric strong{display:block;font-size:2rem;color:#102b4e;margin-top:8px}
        .row{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:8px 0 18px}.btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border-radius:8px;border:0;background:#1769aa;color:white;font:inherit;font-weight:650;cursor:pointer}
        .btn:hover{background:#105487}.btn.secondary{background:#e8f0f8;color:#173b60}.btn.secondary:hover{background:#d9e7f4}
        .tablewrap{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:13px 12px;border-bottom:1px solid #e8edf3;white-space:nowrap}th{font-size:.82rem;text-transform:uppercase;letter-spacing:.04em;color:#687992}
        .badge{display:inline-block;border-radius:99px;background:#edf3f9;padding:5px 10px;font-size:.82rem;color:#355779}
        .flash{padding:12px 15px;border-radius:9px;background:#e9f8ef;color:#17633b;margin-bottom:18px}.errors{padding:12px 15px;border-radius:9px;background:#fff0ee;color:#a52a21;margin-bottom:18px}
        .formgrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{display:flex;flex-direction:column;gap:7px}.field.full{grid-column:1/-1}label{font-weight:650;color:#31435d;font-size:.92rem}
        input,select,textarea{width:100%;border:1px solid #cbd6e3;border-radius:8px;padding:11px 12px;font:inherit;color:#172033;background:white}textarea{min-height:110px;resize:vertical}
        input:focus,select:focus,textarea:focus{outline:3px solid #b8dcf5;border-color:#368ac4}.hint{font-size:.85rem;color:#738198}
        .empty{color:#6b7890;padding:24px 0;text-align:center}.pagination{margin-top:16px}.authbody{display:grid;place-items:center;min-height:100vh;padding:24px}.authbox{width:min(100%,440px);padding:34px}.authbox .brand{color:#1769aa;margin-bottom:28px}.authbox h1{font-size:1.8rem}.authbox .field{margin:16px 0}
        @media(max-width:720px){.nav{align-items:flex-start;flex-direction:column}.who{display:none}.grid{grid-template-columns:1fr}.formgrid{grid-template-columns:1fr}.field.full{grid-column:auto}.wrap{padding:24px 16px 40px}}
    </style>
</head>
<body>
<header class="top">
    <nav class="nav" aria-label="Navegación principal">
        <a class="brand" href="{{ route('dashboard') }}">TESI · Bitácora</a>
        <div class="navlinks">
            @can('dashboard.ver_global')
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Panel</a>
            @elsecan('dashboard.ver_departamento')
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Panel</a>
            @endcan
            @can('bitacora.ver')
                <a class="{{ request()->routeIs('atenciones.*') ? 'active' : '' }}" href="{{ route('atenciones.index') }}">Atenciones</a>
            @endcan
            @can('estudiantes.ver')
                <a class="{{ request()->routeIs('estudiantes.*') ? 'active' : '' }}" href="{{ route('estudiantes.index') }}">Estudiantes</a>
            @endcan
            @can('seguimiento.ver')
                <a class="{{ request()->routeIs('seguimiento.*') ? 'active' : '' }}" href="{{ route('seguimiento.index') }}">Seguimiento</a>
            @endcan
            @can('documentos.ver_historial')
                <a class="{{ request()->routeIs('documentos.*') ? 'active' : '' }}" href="{{ route('documentos.index') }}">Documentos</a>
            @endcan
            @can('catalogos.administrar')
                <a class="{{ request()->routeIs('personal.*') ? 'active' : '' }}" href="{{ route('personal.index') }}">Personal</a>
                <a class="{{ request()->routeIs('catalogos.*') ? 'active' : '' }}" href="{{ route('catalogos.index') }}">Catálogos</a>
            @endcan
            @can('usuarios.administrar')
                <a class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">Usuarios</a>
            @endcan
            @can('auditoria.ver')
                <a class="{{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="{{ route('auditoria.index') }}">Auditoría</a>
            @endcan
            <span class="who">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Salir</button></form>
        </div>
    </nav>
</header>
<main class="wrap">
    @if (session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors" role="alert">
            <strong>Revisa la información:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
