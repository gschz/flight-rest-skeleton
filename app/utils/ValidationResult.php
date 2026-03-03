<?php

declare(strict_types=1);

namespace app\utils;

/**
 * DTO inmutable que representa el resultado de una operación de validación.
 *
 * Nunca lanza excepciones — los llamadores siempre reciben un objeto resultado.
 * Las instancias se crean únicamente a través de los factory methods pass() y fail().
 */
final readonly class ValidationResult
{
    /**
     * @param array<string, string> $fieldErrors
     */
    private function __construct(
        private bool $valid,
        private array $fieldErrors,
    ) {
        //
    }

    /**
     * Retorna un resultado exitoso (sin errores).
     */
    public static function pass(): self
    {
        return new self(true, []);
    }

    /**
     * Retorna un resultado fallido con los mensajes de error por campo.
     *
     * @param array<string, string> $errors
     */
    public static function fail(array $errors): self
    {
        return new self(false, $errors);
    }

    /**
     * Indica si todas las reglas pasaron.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Mensajes de error por campo; array vacío cuando la validación es válida.
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->fieldErrors;
    }
}
