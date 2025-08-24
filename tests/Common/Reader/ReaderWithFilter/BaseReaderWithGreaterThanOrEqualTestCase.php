<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithGreaterThanOrEqualTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'integer' => [new GreaterThanOrEqual('number', 3), [3, 4, 5]];
        yield 'float' => [new GreaterThanOrEqual('balance', 100.0), [3, 4]];
        yield 'datetime' => [new GreaterThanOrEqual('born_at', new DateTimeImmutable('1990-01-01')), [5]];
        yield 'datetime 2' => [new GreaterThanOrEqual('born_at', new DateTimeImmutable('1991-01-01')), []];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(GreaterThanOrEqual $filter, array $expectedFixtureNumbers): void
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
