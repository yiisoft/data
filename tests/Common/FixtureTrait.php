<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common;

trait FixtureTrait
{
    protected static $fixtures = [
        ['number' => 1, 'email' => 'foo@bar', 'balance' => 10.25, 'born_at' => null],
        ['number' => 2, 'email' => 'bar@foo', 'balance' => 1.0, 'born_at' => null],
        ['number' => 3, 'email' => 'seed@beat', 'balance' => 100.0, 'born_at' => null],
        ['number' => 4, 'email' => 'the@best', 'balance' => 500.0, 'born_at' => null],
        ['number' => 5, 'email' => 'test@test', 'balance' => 42.0, 'born_at' => '1990-01-01'],
    ];

    protected function assertFixtures(array $expectedFixtureIndexes, array $actualFixtures): void
    {
        $expectedFixtures = [];
        foreach ($expectedFixtureIndexes as $index) {
            $expectedFixtures[$index] = $this->getFixture($index);
        }

        $this->assertSame($expectedFixtures, $actualFixtures);
    }

    protected function getFixture(int $index): array
    {
        return self::$fixtures[$index];
    }
}
