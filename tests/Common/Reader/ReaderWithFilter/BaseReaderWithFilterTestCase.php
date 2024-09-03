<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Tests\TestCase;

abstract class BaseReaderWithFilterTestCase extends TestCase
{
    protected static $fixtures = [
        ['number' => 1, 'email' => 'foo@bar', 'balance' => 10.25, 'born_at' => null],
        ['number' => 2, 'email' => 'bar@foo', 'balance' => 1.0, 'born_at' => null],
        ['number' => 3, 'email' => 'seed@beat', 'balance' => 100.0, 'born_at' => null],
        ['number' => 4, 'email' => 'the@best', 'balance' => 500.0, 'born_at' => null],
        ['number' => 5, 'email' => 'test@test', 'balance' => 42.0, 'born_at' => '1990-01-01'],
    ];

    abstract protected function getReader(): DataReaderInterface;

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
                $fixture['number'] = (int) $fixture['number'];
                $fixture['balance'] = (float) $fixture['balance'];

                return $fixture;
            },
            $actualFixtures,
        );
        $this->assertSame(array_values($expectedFixtures), array_values($actualFixtures));
    }
}
