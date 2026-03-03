<?php

declare(strict_types=1);

namespace app\utils;

use flight\Engine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /** @param Engine<object> $engine */
    public static function success(Engine $engine, mixed $data, int $status = 200): void
    {
        $engine->json(['success' => true, 'data' => $data], $status);
    }

    /** @param Engine<object> $engine */
    public static function error(Engine $engine, string $message, int $status = 400, mixed $errors = null): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        $engine->json($payload, $status);
    }

    /**
     * Genera una respuesta JSON paginada a partir de un LengthAwarePaginator de Eloquent.
     *
     * @param Engine<object> $engine
     * @param LengthAwarePaginator<int, mixed> $lengthAwarePaginator
     */
    public static function fromPaginator(Engine $engine, LengthAwarePaginator $lengthAwarePaginator): void
    {
        $engine->json([
            'success' => true,
            'data'    => $lengthAwarePaginator->items(),
            'meta'    => [
                'total'    => $lengthAwarePaginator->total(),
                'page'     => $lengthAwarePaginator->currentPage(),
                'per_page' => $lengthAwarePaginator->perPage(),
                'pages'    => $lengthAwarePaginator->lastPage(),
                'from'     => $lengthAwarePaginator->firstItem() ?? 0,
                'to'       => $lengthAwarePaginator->lastItem() ?? 0,
                'has_more' => $lengthAwarePaginator->hasMorePages(),
            ],
            'links'   => [
                'first' => $lengthAwarePaginator->url(1),
                'last'  => $lengthAwarePaginator->url($lengthAwarePaginator->lastPage()),
                'prev'  => $lengthAwarePaginator->previousPageUrl(),
                'next'  => $lengthAwarePaginator->nextPageUrl(),
            ],
        ]);
    }
}
