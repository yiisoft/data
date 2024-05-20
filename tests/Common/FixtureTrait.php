<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common;

trait FixtureTrait
{
    protected static $fixtures = [
        ['number' => 1, 'email' => 'foo@bar', 'balance' => 10.25, 'born_at' => null],
        ['number' => 2, 'email' => 'bar@foo', 'balance' => 1, 'born_at' => null],
        ['number' => 3, 'email' => 'seed@beat', 'balance' => 100, 'born_at' => null],
        ['number' => 4, 'email' => 'the@best', 'balance' => 500, 'born_at' => null],
        ['number' => 5, 'email' => 'test@test', 'balance' => 42, 'born_at' => '1990-01-01'],
    ];

    protected function assertFixtures(array $expectedFixtureIndexes, array $actualFixtures): void
    {
        $expectedFixtures = array_map(
            static fn (int $expectedNumber) => self::$fixtures[$expectedNumber],
            $expectedFixtureIndexes,
        );
        $actualFixtures = array_map(
            static function (array|object $fixture): array {
                if (is_object($fixture)) {
                    $fixture = json_decode(json_encode($fixture), associative: true);
                }

                unset($fixture['id']);

                return $fixture;
            },
            $actualFixtures,
        );
        $this->assertSame(array_values($expectedFixtures), array_values($actualFixtures));
    }
}
