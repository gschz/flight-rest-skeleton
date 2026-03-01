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
        $ds = DIRECTORY_SEPARATOR;
        $root = '';
        if (defined('PROJECT_ROOT')) {
            $constant = constant('PROJECT_ROOT');
            if (is_string($constant)) {
                $root = $constant;
            }
        }

        if ($root === '') {
            $cwd = getcwd();
            $root = is_string($cwd) ? $cwd : '';
        }

        $base = rtrim($root, $ds . '/\\');
        $relativePath = ltrim($path, $ds . '/\\');

        if ($relativePath === '') {
            return $base;
        }

        return $base . $ds . $relativePath;
    }
}
