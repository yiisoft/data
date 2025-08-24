<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithLessThanTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'integer' => [new LessThan('number', 3), [1, 2]];
        yield 'float' => [new LessThan('balance', 50.0), [1, 2, 5]];
        yield 'datetime' => [new LessThan('born_at', new DateTimeImmutable('1991-01-01')), [5]];
        yield 'datetime 2' => [new LessThan('born_at', new DateTimeImmutable('1990-01-01')), []];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(LessThan $filter, array $expectedFixtureNumbers): void
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
