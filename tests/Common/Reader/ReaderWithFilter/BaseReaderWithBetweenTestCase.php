<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;
use Yiisoft\Data\Tests\Support\StringableValue;

abstract class BaseReaderWithBetweenTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'stringable' => [new Between('email', new StringableValue('ta'), new StringableValue('tz')), [4, 5]];
        yield 'float' => [new Between('balance', 10.25, 100.0), [1, 3, 5]];
        yield 'datetime' => [new Between('born_at', new DateTimeImmutable('1989-01-01'), new DateTimeImmutable('1991-01-01')), [5]];
        yield 'datetime 2' => [new Between('born_at', new DateTimeImmutable('1990-01-02'), new DateTimeImmutable('1990-01-03')), []];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(Between $filter, array $expectedFixtureNumbers): void
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
