<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    /**
     * Obtiene la ruta base de la instalación.
     *
     * Devuelve la ruta raíz del proyecto, concatenando opcionalmente una ruta relativa.
     * Útil para referencias absolutas de archivos dentro del proyecto.
     *
     * @param string $path Ruta relativa a añadir a la ruta base (opcional).
     * @return string Ruta absoluta normalizada.
     */
    function base_path(string $path = ''): string
    {
        if ($path === '') {
            return PROJECT_ROOT;
        }

        $base = rtrim(PROJECT_ROOT, DIRECTORY_SEPARATOR . '/\\');
        $relativePath = ltrim($path, DIRECTORY_SEPARATOR . '/\\');

        return $base . DIRECTORY_SEPARATOR . $relativePath;
    }
}
