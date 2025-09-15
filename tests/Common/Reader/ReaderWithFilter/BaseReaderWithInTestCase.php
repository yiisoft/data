<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;
use Yiisoft\Data\Tests\Support\StringableValue;

abstract class BaseReaderWithInTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): iterable
    {
        yield 'int' => [new In('number', [2, 3]), [1, 2]];
        yield 'stringable' => [
            new In('email', [new StringableValue('seed@beat'), new StringableValue('the@best')]),
            [2, 3],
        ];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(In $filter, array $expected): void
    {
        $reader = $this->getReader()->withFilter($filter);
        $this->assertFixtures($expected, $reader->read());
    }
}
