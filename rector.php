<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withCache(
        cacheDirectory: __DIR__ . '/app/cache/rector',
        cacheClass: FileCacheStorage::class,
    )
    ->withPaths([
        __DIR__ . '/app',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        naming: true,
        privatization: true,
        earlyReturn: true,
        typeDeclarations: true,
        rectorPreset: true,
    )
    ->withComposerBased(phpunit: true)
    ->withAttributesSets()
    ->withSkip([
        // El parámetro $params es requerido por el contrato de FlightPHP
        // aunque no se use en el cuerpo del método
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__ . '/app/middlewares',
        ],
    ])
    ->withPhpSets(php84: true);
