#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Ejecuta migraciones Phinx automáticamente en deploys de producción/Heroku.
 * Solo se ejecuta cuando DATABASE_URL está definido (Heroku) o APP_ENV=production.
 *
 * Usage:
 *   php bin/migrate-if-production.php
 *
 * Examples:
 *   php bin/migrate-if-production.php                     # se ejecuta automáticamente vía post-install-cmd
 *   APP_ENV=production php bin/migrate-if-production.php  # forzar modo producción
 */

$projectRoot = dirname(__DIR__);
$appEnv      = (string)(getenv('APP_ENV') ?: 'development');
$dbUrl       = (string)(getenv('DATABASE_URL') ?: '');

$isProduction = $appEnv === 'production';
$hasDbUrl     = $dbUrl !== '';

if (!$isProduction && !$hasDbUrl) {
    echo "[migrate] Omitiendo migraciones (no es un entorno de producción/heroku).\n";
    echo "[migrate] APP_ENV      = {$appEnv}\n";
    echo "[migrate] DATABASE_URL = (no definido)\n";
    exit(0);
}

echo "[migrate] Entorno detectado: " . ($hasDbUrl ? 'Heroku/DATABASE_URL' : 'production') . "\n";
echo "[migrate] APP_ENV      = {$appEnv}\n";
echo "[migrate] DATABASE_URL = " . ($hasDbUrl ? '(definido)' : '(no definido)') . "\n\n";
echo "[migrate] Ejecutando migraciones Phinx...\n";

$phinxBin  = $projectRoot . '/vendor/bin/phinx';
$phinxConf = $projectRoot . '/phinx.php';

$exitCode = 0;
passthru(
    sprintf(
        'php %s migrate -c %s',
        escapeshellarg($phinxBin),
        escapeshellarg($phinxConf)
    ),
    $exitCode
);

if ($exitCode !== 0) {
    echo "\n[migrate] Error: las migraciones fallaron con código de salida {$exitCode}.\n";
    exit($exitCode);
}

echo "\n[migrate] Migraciones completadas exitosamente.\n";
