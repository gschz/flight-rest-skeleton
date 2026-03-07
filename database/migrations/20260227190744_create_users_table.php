<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
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
        $table = $this->table('users');
        $table
            ->addColumn(
                'name',
                'string',
                ['limit' => 255]
            )
            ->addColumn(
                'email',
                'string',
                ['limit' => 255]
            )
            ->addColumn(
                'password',
                'string',
                ['limit' => 255, 'null' => false]
            )
            ->addColumn(
                'created_at',
                'datetime',
                ['null' => true, 'default' => null]
            )
            ->addColumn(
                'updated_at',
                'datetime',
                ['null' => true, 'default' => null]
            )
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}
