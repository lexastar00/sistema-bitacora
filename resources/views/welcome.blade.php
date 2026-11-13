<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistema de Bitácora TESI</title>
        <style>
            :root {
                --bg: #0f172a;
                --panel: #111827;
                --panel-soft: #1f2937;
                --primary: #22c55e;
                --secondary: #38bdf8;
                --text: #e5e7eb;
                --muted: #94a3b8;
                --shadow: rgba(15, 23, 42, 0.35);
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: Arial, Helvetica, sans-serif;
                background: linear-gradient(135deg, #020617 0%, #111827 45%, #0b1120 100%);
                color: var(--text);
            }

            .container {
                width: min(100%, 960px);
                margin: 32px auto;
                padding: 32px 24px;
            }

            .card {
                background: rgba(17, 24, 39, 0.82);
                border: 1px solid rgba(148, 163, 184, 0.18);
                border-radius: 24px;
                box-shadow: 0 20px 60px var(--shadow);
                overflow: hidden;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 18px 24px;
                background: rgba(15, 23, 42, 0.95);
                border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            }

            .brand {
                font-size: 0.95rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--secondary);
            }

            .status {
                padding: 8px 12px;
                border-radius: 999px;
                font-size: 0.78rem;
                border: 1px solid rgba(34, 197, 94, 0.4);
                background: rgba(34, 197, 94, 0.12);
                color: #bbf7d0;
            }

            .content {
                padding: 48px 24px 36px;
                text-align: center;
            }

            .badge {
                display: inline-block;
                padding: 8px 14px;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                background: rgba(56, 189, 248, 0.12);
                border: 1px solid rgba(56, 189, 248, 0.4);
                color: #bae6fd;
            }

            h1 {
                margin: 20px 0 14px;
                font-size: clamp(2rem, 4vw, 3.4rem);
                line-height: 1.1;
            }

            .lead {
                max-width: 700px;
                margin: 0 auto;
                color: var(--muted);
                font-size: 1.08rem;
                line-height: 1.7;
            }

            .grid {
                margin-top: 28px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 16px;
            }

            .item {
                background: rgba(31, 41, 55, 0.85);
                border: 1px solid rgba(148, 163, 184, 0.12);
                border-radius: 16px;
                padding: 22px 18px;
                text-align: left;
            }

            .item strong {
                display: block;
                margin-bottom: 8px;
                font-size: 1rem;
                color: white;
            }

            .item span {
                color: var(--muted);
                line-height: 1.6;
                font-size: 0.96rem;
            }

            .footer {
                margin-top: 28px;
                color: var(--muted);
                font-size: 0.92rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="topbar">
                    <div class="brand">TESI · Sistemas</div>
                    <div class="status">Sistema en desarrollo</div>
                </div>

                <main class="content">
                    <div class="badge">Sistema de Bitácora</div>
                    <h1>División de Ingeniería en Sistemas Computacionales</h1>
                    <p class="lead">
                        Este proyecto aún no tiene la interfaz funcional completa del sistema; por eso la vista inicial
                        mostraba el template predeterminado de Laravel. La base del proyecto ya está levantada y lista para
                        continuar con el desarrollo de módulos, autenticación y bitácoras.
                    </p>

                    <div class="grid">
                        <div class="item">
                            <strong>Estado</strong>
                            <span>Proyecto base Laravel funcionando correctamente y listo para continuar con la implementación del sistema real.</span>
                        </div>
                        <div class="item">
                            <strong>Base de datos</strong>
                            <span>Configurada con SQLite local y migraciones/seeders ejecutados correctamente.</span>
                        </div>
                        <div class="item">
                            <strong>Próximo paso</strong>
                            <span>Crear las pantallas reales de login, dashboard y módulos de atención/seguimiento.</span>
                        </div>
                    </div>

                    <div class="footer">
                        Laravel v{{ app()->version() }} · {{ config('app.name') }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
