<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testFlightAppCanBeCreated(): void
    {
        $app = Flight::app();

        self::assertInstanceOf(\flight\Engine::class, $app);
    }
}

