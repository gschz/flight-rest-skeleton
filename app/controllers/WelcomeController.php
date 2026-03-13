<?php

declare(strict_types=1);

namespace app\controllers;

use flight\Engine;

final readonly class WelcomeController
{
    /** @param Engine<object> $engine */
    public function __construct(private Engine $engine)
    {
        //
    }

    public function index(): void
    {
        $dbUrl    = (string) (getenv('DATABASE_URL') ?: '');
        $appEnv   = defined('APP_ENV') && is_string(APP_ENV) ? APP_ENV : 'production';
        $isCloud  = $dbUrl !== '' || $appEnv === 'production';
        $dbDriver = $dbUrl !== '' ? 'pgsql' : (string) (getenv('DB_CONNECTION') ?: 'sqlite');
        $hasDocs  = !(defined('IS_PRODUCTION') && IS_PRODUCTION);

        $this->engine->render('welcome', [
            'title'    => 'FlightPHP REST Skeleton',
            'appEnv'   => $appEnv,
            'isDev'    => $appEnv === 'development',
            'isCloud'  => $isCloud,
            'dbDriver' => $dbDriver,
            'hasDocs'  => $hasDocs,
        ]);
    }
}
