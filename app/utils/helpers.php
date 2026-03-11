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

if (!function_exists('html_escape')) {
    /**
     * Escapa una cadena para salida HTML segura.
     *
     * @param string $value Valor a escapar.
     * @return string Cadena escapada.
     */
    function html_escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

if (!function_exists('is_valid_string')) {
    /**
     * Comprueba si un valor es una cadena no vacía.
     *
     * Útil para el narrowing de tipos en templates y contextos donde PHPStan
     * recibe variables de tipo mixed (e.g. Flight::render extracts).
     *
     * @phpstan-assert-if-true non-empty-string $value
     * @param mixed $value Valor a comprobar.
     */
    function is_valid_string(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}
