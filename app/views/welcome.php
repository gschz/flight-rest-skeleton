<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlightPHP REST Skeleton</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen">
    <?php
    $appEnv   = defined('APP_ENV') && is_string(APP_ENV) ? APP_ENV : 'production';
    $isDev    = $appEnv === 'development';
    $dbUrl    = (string)(getenv('DATABASE_URL') ?: '');
    $dbDriver = $dbUrl !== '' ? 'pgsql' : (string)(getenv('DB_CONNECTION') ?: 'sqlite');
    $isCloud  = $dbUrl !== '' || $appEnv === 'production';
    ?>
    <div class="max-w-4xl mx-auto px-6 py-14">

        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">FlightPHP REST Skeleton</h1>
                <p class="text-sm text-slate-500 mt-1">Pure REST API · v0.1.0-alpha</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">FlightPHP v3</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">Eloquent</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">Phinx</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">Runway</span>
                <?php if ($isCloud) : ?>
                    <span class="px-2.5 py-1 rounded-full bg-slate-900 text-white">producción</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-[5fr,3fr]">

            <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <h2 class="font-semibold">Estado del entorno</h2>
                <p class="text-sm text-slate-500 mt-0.5">Información del runtime actual.</p>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Entorno</p>
                        <p class="mt-1.5 font-semibold capitalize">
                            <?= htmlspecialchars($appEnv, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">PHP</p>
                        <p class="mt-1.5 font-semibold">
                            <?php if ($isDev) : ?>
                                <?= htmlspecialchars(phpversion(), ENT_QUOTES, 'UTF-8') ?>
                            <?php else : ?>
                                <span class="text-slate-400 font-normal text-sm">oculto</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Base de datos</p>
                        <p class="mt-1.5 font-semibold">
                            <?php if ($isDev || $isCloud) : ?>
                                <?= htmlspecialchars(strtoupper($dbDriver), ENT_QUOTES, 'UTF-8') ?>
                            <?php else : ?>
                                <span class="text-slate-400 font-normal text-sm">oculto</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="/api/v1/users"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 transition"
                        target="_blank" rel="noreferrer">
                        GET /api/v1/users
                    </a>
                    <a href="/health"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:border-slate-400 transition"
                        target="_blank" rel="noreferrer">
                        /health
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-sm">Stack</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                    <?php
                    $stack = [
                        'Flight Framework v3'  => 'https://docs.flightphp.com/es/v3/',
                        'Eloquent ORM'         => 'https://laravel.com/docs/12.x/eloquent',
                        'Phinx Migrations'     => 'https://phinx.org/',
                        'Runway CLI'           => 'https://github.com/flightphp/runway',
                        'Tracy Debugger'       => 'https://tracy.nette.org/',
                        'PHPStan (Level Max)'  => 'https://phpstan.org/',
                        'PHP-CS-Fixer'         => 'https://cs.symfony.com/',
                        'PHPUnit'              => 'https://phpunit.de/',
                    ];
    foreach ($stack as $name => $url) : ?>
                        <li class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300 shrink-0"></span>
                            <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank" rel="noreferrer"
                                class="hover:text-slate-900 transition">
                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-sm mb-4">Endpoints</h3>
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 text-xs font-mono">
                <?php
                $endpoints = [
    ['GET',    '/health'],
    ['GET',    '/api/v1/users'],
    ['GET',    '/api/v1/users/:id'],
    ['POST',   '/api/v1/users'],
    ['PUT',    '/api/v1/users/:id'],
    ['DELETE', '/api/v1/users/:id'],
                ];
    $methodColors = [
        'GET'    => 'text-sky-700 bg-sky-50 border-sky-100',
        'POST'   => 'text-emerald-700 bg-emerald-50 border-emerald-100',
        'PUT'    => 'text-amber-700 bg-amber-50 border-amber-100',
        'DELETE' => 'text-red-700 bg-red-50 border-red-100',
    ];
    foreach ($endpoints as [$method, $path]) :
        $color = $methodColors[$method];
        ?>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                        <span class="<?= $color ?> border rounded px-1.5 py-0.5 shrink-0">
                            <?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="text-slate-600 truncate">
                            <?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <a href="https://github.com/gschz/flight-rest-skeleton"
                target="_blank" rel="noreferrer"
                class="hover:text-slate-600 underline underline-offset-2 transition">
                gschz/flight-rest-skeleton
            </a>
            <a href="https://github.com/gschz/flight-rest-skeleton/blob/main/README.md"
                target="_blank" rel="noreferrer"
                class="hover:text-slate-600 underline underline-offset-2 transition">
                README.md
            </a>
        </div>
    </div>
</body>

</html>