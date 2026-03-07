<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;


/**
 * Crea la tabla `refresh_tokens` para el sistema de autenticación JWT.
 *
 * Almacena tokens de refresco opacos asociados a usuarios.
 * Los tokens pueden ser revocados explícitamente al hacer logout.
 */

final class CreateRefreshTokensTable extends AbstractMigration
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
        $table = $this->table('refresh_tokens');
        $table
            ->addColumn(
                'user_id',
                'integer',
                ['null' => false]
            )
            ->addColumn(
                'token',
                'string',
                ['limit' => 255, 'null' => false]
            )
            ->addColumn(
                'expires_at',
                'datetime',
                ['null' => false]
            )
            ->addColumn(
                'revoked',
                'integer',
                ['default' => 0, 'null' => false]
            )
            ->addColumn(
                'created_at',
                'datetime',
                ['null' => true, 'default' => null]
            )
            ->addIndex(['token'], ['unique' => true])
            ->addIndex(['user_id'])
            ->create();
    }
}
