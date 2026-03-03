<?php

declare(strict_types=1);

namespace app\utils;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/**
 * Wrapper ligero sobre Respect\Validation que retorna un ValidationResult tipado.
 *
 * Todos los métodos son estáticos — esta clase no está pensada para ser instanciada.
 * Compatible con PHPStan level max: ningún tipo mixed escapa hacia los llamadores.
 */
final class Validator
{
    private function __construct()
    {
        //
    }

    /**
     * Valida un array asociativo de datos contra un conjunto de reglas nombradas.
     *
     * @param array<string, mixed>       $data  Datos de entrada indexados por nombre de campo.
     * @param array<string, Validatable> $rules Reglas de Respect\Validation indexadas por el mismo nombre de campo.
     *
     * @return ValidationResult Aprobado (sin errores) o fallido (mensajes por campo).
     */
    public static function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            try {
                $rule->assert($value);
            } catch (NestedValidationException $e) {
                $messages = $e->getMessages();
                $first    = reset($messages);
                $errors[$field] = ($first !== false) ? $first : 'Valor inválido';
            }
        }

        if ($errors !== []) {
            return ValidationResult::fail($errors);
        }

        return ValidationResult::pass();
    }
}
