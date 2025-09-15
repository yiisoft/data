<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;
use Yiisoft\Data\Tests\Support\StringableValue;

abstract class BaseReaderWithEqualsTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'integer' => [new Equals('number', 2), [2]];
        yield 'float' => [new Equals('balance', 10.25), [1]];
        yield 'string' => [new Equals('email', 'the@best'), [4]];
        yield 'stringable' => [new Equals('email', new StringableValue('the@best')), [4]];
        yield 'datetime' => [new Equals('born_at', new DateTimeImmutable('1990-01-01')), [5]];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(Equals $filter, array $expectedFixtureNumbers): void
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
