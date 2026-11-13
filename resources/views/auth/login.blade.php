<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Sistema de Bitácora TESI</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(145deg,#102b4e,#07172b);font-family:Inter,Segoe UI,Arial,sans-serif;color:#172033}
        .box{width:min(100%,440px);background:white;border-radius:18px;padding:36px;box-shadow:0 24px 70px #020b18aa}.brand{font-size:.9rem;font-weight:750;letter-spacing:.08em;text-transform:uppercase;color:#1769aa}
        h1{font-size:1.9rem;color:#102b4e;margin:28px 0 8px}.intro{color:#687992;margin:0 0 26px;line-height:1.5}.field{margin:17px 0}label{display:block;font-weight:650;font-size:.92rem;margin-bottom:7px;color:#31435d}
        input{width:100%;border:1px solid #cbd6e3;border-radius:8px;padding:12px;font:inherit}input:focus{outline:3px solid #b8dcf5;border-color:#368ac4}
        .remember{display:flex;align-items:center;gap:9px;color:#52637c;font-size:.9rem}.remember input{width:auto}.btn{width:100%;border:0;border-radius:8px;padding:12px;background:#1769aa;color:white;font:inherit;font-weight:700;cursor:pointer;margin-top:22px}.btn:hover{background:#105487}
        .errors{padding:12px 14px;background:#fff0ee;color:#a52a21;border-radius:8px;margin:18px 0}.footer{text-align:center;color:#8a97a9;font-size:.82rem;margin-top:26px}
    </style>
</head>
<body>
    <main class="box">
        <div class="brand">TESI · Sistemas</div>
        <h1>Iniciar sesión</h1>
        <p class="intro">Accede al Sistema de Gestión y Bitácora del Área de Sistemas.</p>
        @if ($errors->any())
            <div class="errors" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Mantener sesión iniciada</label>
            <button class="btn" type="submit">Entrar al sistema</button>
        </form>
        <div class="footer">División de Ingeniería en Sistemas Computacionales</div>
    </main>
</body>
</html>
