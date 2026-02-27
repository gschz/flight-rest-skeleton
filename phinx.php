<?php

declare(strict_types=1);

$dbConnection = (string)(getenv('DB_CONNECTION') ?: 'sqlite');
$projectRoot = __DIR__;

if ($dbConnection === 'pgsql') {
    $adapterConfig = [
        'adapter'  => 'pgsql',
        'host'     => (string)(getenv('DB_HOST') ?: '127.0.0.1'),
        'name'     => (string)(getenv('DB_DATABASE') ?: 'app'),
        'user'     => (string)(getenv('DB_USERNAME') ?: 'postgres'),
        'pass'     => (string)(getenv('DB_PASSWORD') ?: ''),
        'port'     => (int)(getenv('DB_PORT') ?: 5432),
        'charset'  => 'utf8',
    ];
} else {
    $adapterConfig = [
        'adapter' => 'sqlite',
        'name'    => (string)(getenv('DB_DATABASE') ?: $projectRoot . '/database/database.sqlite'),
    ];
}

return [
    'paths' => [
        'migrations' => $projectRoot . '/database/migrations',
        'seeds'      => $projectRoot . '/database/seeders',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'default',
        'default'                 => $adapterConfig,
        'testing' => [
            'adapter' => 'sqlite',
            'name'    => ':memory:',
        ],
    ],
    'version_order' => 'creation',
];
