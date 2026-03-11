<?php

// Variables inyectadas por Flight::render() — narrowing de tipos para templates
$title    = is_valid_string($title)    ? $title    : '';
$appEnv   = is_valid_string($appEnv)   ? $appEnv   : 'development';
$dbDriver = is_valid_string($dbDriver) ? $dbDriver : 'sqlite';
$isDev    = is_bool($isDev) && $isDev;
$isCloud  = is_bool($isCloud) && $isCloud;
$hasDocs  = is_bool($hasDocs) && $hasDocs;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen">
    <div class="max-w-4xl mx-auto px-6 py-14">

        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight"><?= html_escape($title) ?></h1>
                <p class="text-sm text-slate-500 mt-1">REST API skeleton · v1.0.0-beta</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">FlightPHP v3</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">JWT</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">OpenAPI</span>
                <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">Eloquent</span>
                <?php if ($isCloud) : ?>
                    <span class="px-2.5 py-1 rounded-full bg-slate-900 text-white">producción</span>
                <?php else : ?>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">development</span>
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
                            <?= html_escape($appEnv) ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">PHP</p>
                        <p class="mt-1.5 font-semibold">
                            <?php if ($isDev) : ?>
                                <?= html_escape(phpversion()) ?>
                            <?php else : ?>
                                <span class="text-slate-400 font-normal text-sm">oculto</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Base de datos</p>
                        <p class="mt-1.5 font-semibold">
                            <?= html_escape(strtoupper($dbDriver)) ?>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="/health"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 transition"
                        target="_blank" rel="noreferrer">
                        /health
                    </a>
                    <?php if ($hasDocs) : ?>
                        <a href="/docs"
                            class="inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-700 hover:bg-violet-100 transition"
                            target="_blank" rel="noreferrer">
                            Swagger UI
                        </a>
                        <a href="/docs/redoc"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:border-slate-400 transition"
                            target="_blank" rel="noreferrer">
                            ReDoc
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-sm">Stack</h3>
                <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm text-slate-600">
                    <?php
                    $stack = [
                        'Flight Framework v3'  => 'https://docs.flightphp.com/es/v3/',
                        'Eloquent ORM'         => 'https://laravel.com/docs/12.x/eloquent',
                        'Firebase JWT'         => 'https://github.com/firebase/php-jwt',
                        'Phinx Migrations'     => 'https://phinx.org/',
                        'Swagger-PHP'          => 'https://zircote.com/swagger-php/',
                        'Runway CLI'           => 'https://github.com/flightphp/runway',
                        'Tracy Debugger'       => 'https://tracy.nette.org/',
                        'PHPStan (Level Max)'  => 'https://phpstan.org/',
                        'PHPUnit'              => 'https://phpunit.de/',
                    ];
                    foreach ($stack as $name => $url) : ?>
                        <li class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300 shrink-0"></span>
                            <a href="<?= html_escape($url) ?>"
                                target="_blank" rel="noreferrer"
                                class="hover:text-slate-900 transition">
                                <?= html_escape($name) ?>
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
                $methodColors = [
                    'GET'    => 'text-sky-700 bg-sky-50 border-sky-100',
                    'POST'   => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                    'PUT'    => 'text-amber-700 bg-amber-50 border-amber-100',
                    'DELETE' => 'text-red-700 bg-red-50 border-red-100',
                ];

                $groups = [
                    'Sistema' => [
                        ['GET', '/health'],
                    ],
                    'Auth' => [
                        ['POST', '/api/v1/auth/login'],
                        ['POST', '/api/v1/auth/refresh'],
                        ['POST', '/api/v1/auth/logout'],
                    ],
                    'Usuarios' => [
                        ['GET',    '/api/v1/users'],
                        ['GET',    '/api/v1/users/:id'],
                        ['POST',   '/api/v1/users'],
                        ['PUT',    '/api/v1/users/:id'],
                        ['DELETE', '/api/v1/users/:id'],
                    ],
                ];

                if ($hasDocs) {
                    $groups['Docs (dev)'] = [
                        ['GET', '/docs'],
                        ['GET', '/docs/redoc'],
                        ['GET', '/api-docs/openapi.json'],
                    ];
                }

                foreach ($groups as $groupName => $endpoints) : ?>
                    <div class="col-span-full mt-2 first:mt-0">
                        <p class="text-slate-400 uppercase tracking-wide text-[10px] mb-1.5"><?= html_escape($groupName) ?></p>
                    </div>
                    <?php foreach ($endpoints as [$method, $path]) :
                        // @phpstan-ignore nullCoalesce.offset
                        $color = $methodColors[$method] ?? 'text-slate-600 bg-slate-50 border-slate-100'; ?>
                        <div class="flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                            <span class="<?= $color ?> border rounded px-1.5 py-0.5 shrink-0">
                                <?= html_escape($method) ?>
                            </span>
                            <span class="text-slate-600 truncate">
                                <?= html_escape($path) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
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