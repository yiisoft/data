<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithGreaterThanTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'integer' => [new GreaterThan('number', 3), [4, 5]];
        yield 'float' => [new GreaterThan('balance', 50.0), [3, 4]];
        yield 'datetime' => [new GreaterThan('born_at', new DateTimeImmutable('1989-01-01')), [5]];
        yield 'datetime 2' => [new GreaterThan('born_at', new DateTimeImmutable('1990-01-01')), []];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(GreaterThan $filter, array $expectedFixtureNumbers): void
    {
        $expectedFixtureIndexes = array_map(
            static fn(int $number): int => $number - 1,
            $expectedFixtureNumbers,
        );
        $this->assertFixtures(
            $expectedFixtureIndexes,
            $this->getReader()->withFilter($filter)->read(),
        );
    }
}
