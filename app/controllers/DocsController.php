<?php

declare(strict_types=1);

namespace app\controllers;

use app\utils\ApiResponse;
use flight\Engine;

/**
 * Controlador de documentación OpenAPI.
 *
 * Solo disponible en entornos que no sean producción.
 * Registrado condicionalmente en app/routes/web.php.
 *
 * Rutas:
 *   GET /docs                  → Swagger UI (interactivo)
 *   GET /docs/redoc            → ReDoc (solo lectura, más limpio)
 *   GET /api-docs/openapi.json → Spec JSON servido por Flight
 */
final readonly class DocsController
{
    /** @param Engine<object> $engine */
    public function __construct(private Engine $engine)
    {
        //
    }

    /**
     * Renderiza la UI de Swagger.
     * Vista: app/views/docs/swagger.php
     */
    public function swagger(): void
    {
        $this->engine->render('docs/swagger');
    }

    /**
     * Renderiza la UI de ReDoc.
     * Vista: app/views/docs/redoc.php
     */
    public function redoc(): void
    {
        $this->engine->render('docs/redoc');
    }

    /**
     * Sirve el spec OpenAPI como JSON con cabeceras adecuadas.
     *
     * Protección de path traversal: verifica que el archivo resuelto
     * permanezca dentro del directorio public/.
     */
    public function spec(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $publicDir = realpath(__DIR__ . '/../../public');
        $specFile  = realpath(__DIR__ . '/../../public/api-docs/openapi.json');

        if ($publicDir === false || $specFile === false) {
            ApiResponse::error(
                $this->engine,
                'Spec no generado. Ejecuta: composer docs:generate',
                404
            );

            return;
        }

        // Path traversal protection: el archivo debe estar dentro de public/
        if (!str_starts_with($specFile, $publicDir . $ds)) {
            ApiResponse::error(
                $this->engine,
                'Acceso denegado',
                403
            );

            return;
        }

        $content = file_get_contents($specFile);
        if ($content === false) {
            ApiResponse::error(
                $this->engine,
                'Error al leer el spec',
                500
            );

            return;
        }

        $this->engine->response()
            ->header('Content-Type', 'application/json; charset=UTF-8')
            ->header('Cache-Control', 'no-store')
            ->write($content);
        $this->engine->stop();
    }
}
