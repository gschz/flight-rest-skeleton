<?php

declare(strict_types=1);

namespace app\commands;

use flight\commands\AbstractBaseCommand;

/**
 * Genera claves seguras para APP_KEY y JWT_SECRET.
 *
 * Útil al inicializar un nuevo entorno: copia el output al archivo de entorno
 * correspondiente dentro de .envs (por ejemplo .env.local o un template .example).
 */
class MakeKeysCommand extends AbstractBaseCommand
{
    /**
     * @param array<string,mixed> $config Configuración de Runway.
     */
    public function __construct(array $config)
    {
        parent::__construct(
            'make:keys',
            'Genera APP_KEY y JWT_SECRET aleatorios para archivos en .envs (locales o templates).',
            $config
        );
    }

    /**
     * Genera e imprime las claves de entorno requeridas por el proyecto.
     */
    public function execute(): void
    {
        $io = $this->io();

        $appKey    = 'base64:' . base64_encode(random_bytes(32));
        $jwtSecret = bin2hex(random_bytes(32));

        $io->eol();
        $io->comment('Claves generadas — copia estas líneas a tu archivo objetivo dentro de .envs:', true);
        $io->eol();
        $io->writer()->write('APP_KEY=' . $appKey, true);
        $io->writer()->write('JWT_SECRET=' . $jwtSecret, true);
        $io->eol();
        $io->ok('Listo. Nunca compartas estas claves ni las commitees al repositorio.', true);
    }
}
