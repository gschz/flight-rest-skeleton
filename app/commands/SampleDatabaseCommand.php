<?php

declare(strict_types=1);

namespace app\commands;

use flight\commands\AbstractBaseCommand;
use flight\database\PdoWrapper;

class SampleDatabaseCommand extends AbstractBaseCommand
{
    /**
     * Construct
     *
     * @param array<string,mixed> $config JSON config from .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('init:sample-db', 'Creates a sample SQLite database and tables.', $config);
    }

    /**
     * Executes the function
     */
    public function execute(): void
    {
        $io = $this->io();

        $pdoWrapper = new PdoWrapper('sqlite:' . __DIR__ . '/../database.sqlite');

        $io->info('Creating tables...');
        $pdoWrapper->exec('CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY, title TEXT, content TEXT, username TEXT, created_at TEXT, updated_at TEXT)');
        $pdoWrapper->exec('CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY, post_id INTEGER, username TEXT, content TEXT, created_at TEXT, updated_at TEXT)');

        $io->ok('Tables created!', true);
    }
}
