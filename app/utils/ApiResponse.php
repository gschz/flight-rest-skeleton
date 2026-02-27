<?php

declare(strict_types=1);

namespace app\utils;

use flight\Engine;

class ApiResponse
{
    /** @param Engine<object> $app */
    public static function success(Engine $app, mixed $data, int $status = 200): void
    {
        $app->json(['success' => true, 'data' => $data], $status);
    }

    /** @param mixed $errors */
    public static function error(Engine $app, string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        $app->json($payload, $status);
    }

    /**
     * @param mixed $data
     * @param array<string, mixed> $extra
     */
    public static function paginated(Engine $app, mixed $data, int $total, int $page, int $perPage, array $extra = []): void
    {
        $app->json(array_merge([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => (int)ceil($total / max(1, $perPage)),
            ],
        ], $extra));
    }
}
