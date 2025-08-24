<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\LessThanOrEqual;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithLessThanOrEqualTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'integer' => [new LessThanOrEqual('number', 3), [1, 2, 3]];
        yield 'float' => [new LessThanOrEqual('balance', 42.0), [1, 2, 5]];
        yield 'datetime' => [new LessThanOrEqual('born_at', new DateTimeImmutable('1990-01-01')), [5]];
        yield 'datetime 2' => [new LessThanOrEqual('born_at', new DateTimeImmutable('1990-01-02')), [5]];
        yield 'datetime 3' => [new LessThanOrEqual('born_at', new DateTimeImmutable('1989-01-01')), []];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(LessThanOrEqual $filter, array $expectedFixtureNumbers): void
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
