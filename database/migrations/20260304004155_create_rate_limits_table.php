<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;


/**
 * Crea la tabla `rate_limits` para el middleware de rate limiting basado en DB.
 *
 * Columnas:
 *   - key          VARCHAR(255) clave primaria (IP prefijada con 'rl:')
 *   - hits         INTEGER      contador de hits en la ventana actual
 *   - window_start BIGINTEGER   timestamp Unix de inicio de la ventana
 */

final class CreateRateLimitsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('rate_limits', [
            'id'          => false,
            'primary_key' => ['key'],
        ]);

        $table
            ->addColumn('key', 'string', ['limit' => 255])
            ->addColumn('hits', 'integer', ['default' => 0])
            ->addColumn('window_start', 'biginteger')
            ->create();
    }
}
