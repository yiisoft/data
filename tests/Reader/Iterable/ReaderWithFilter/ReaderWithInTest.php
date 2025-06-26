<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\Iterable\ValueReader\PathValueReader;
use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithInTestCase;

use function PHPUnit\Framework\assertSame;

final class ReaderWithInTest extends BaseReaderWithInTestCase
{
    use ReaderTrait;

    public function testNested(): void
    {
        $row1 = ['id' => 1, 'person' => ['name' => 'John', 'age' => 30]];
        $row2 = ['id' => 2, 'person' => ['name' => 'Jane', 'age' => 25]];
        $row3 = ['id' => 3, 'person' => ['name' => 'Doe', 'age' => 40]];
        $reader = new IterableDataReader(
            [$row1, $row2, $row3],
            new PathValueReader(),
        );

        $filter = new In('person.name', ['John', 'Jane']);

        $result = $reader->withFilter($filter)->read();

        assertSame([$row1, $row2], $result);
    }
}
