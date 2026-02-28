<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlightPHP REST Skeleton</title>
    <style>
        :root {
            --primary: #0073aa;
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --text: #333333;
            --border: #e9ecef;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }

        h1 {
            margin-top: 0;
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .status-item {
            background: #f8f9fa;
            border: 1px solid var(--border);
            padding: 1rem;
            border-radius: 8px;
        }

        .status-label {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .status-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .features {
            text-align: left;
            margin-top: 2rem;
            border-top: 1px solid var(--border);
            padding-top: 2rem;
        }

        .features h3 {
            font-size: 1rem;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .features ul {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .features li {
            display: flex;
            align-items: center;
        }

        .features li::before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 0.5rem;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
            margin-top: 1rem;
        }

        .btn:hover {
            background: #005a87;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>FlightPHP REST Skeleton</h1>
        <p class="subtitle">Pure REST API v0.1.0-alpha</p>

        <div class="status-grid">
            <div class="status-item">
                <span class="status-label">Entorno</span>
                <span class="status-value">
                    <?php if (defined('APP_ENV') && APP_ENV === 'development') : ?>
                        <?= htmlspecialchars(APP_ENV, ENT_QUOTES, 'UTF-8') ?>
                    <?php else : ?>
                        Producción
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">PHP</span>
                <span class="status-value">
                    <?php if (defined('APP_ENV') && APP_ENV === 'development') : ?>
                        <?= phpversion() ?>
                    <?php else : ?>
                        Oculto en producción
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Base de Datos</span>
                <span class="status-value">
                    <?php if (defined('APP_ENV') && APP_ENV === 'development') : ?>
                        <?= htmlspecialchars(getenv('DB_CONNECTION') ?: 'sqlite', ENT_QUOTES, 'UTF-8') ?>
                    <?php else : ?>
                        Oculto en producción
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="features">
            <h3>Stack Tecnológico Incluido</h3>
            <ul>
                <li>Flight Framework v3</li>
                <li>Eloquent ORM</li>
                <li>Phinx Migrations</li>
                <li>Tracy Debugger</li>
                <li>PHPStan (Level Max)</li>
                <li>PHP-CS-Fixer</li>
            </ul>
        </div>

        <div style="margin-top: 2rem;">
            <a href="/api/v1/users" class="btn" target="_blank">Probar API Users</a>
            <p style="margin-top: 1rem; font-size: 0.9rem; color: #6c757d;">
                Revisa <code>README.md</code> para comenzar
            </p>
        </div>
    </div>
</body>

</html>