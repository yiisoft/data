<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\OrderHelper;

final class OrderHelperTest extends TestCase
{
    public static function dataReplaceFieldName(): iterable
    {
        yield 'base' => [
            ['new-name' => 'asc', 'age' => 'desc'],
            ['name' => 'asc', 'age' => 'desc'],
            'name',
            'new-name',
        ];
        yield 'not-exist' => [
            ['age' => 'desc', 'name' => 'asc'],
            ['age' => 'desc', 'name' => 'asc'],
            'a',
            'b',
        ];
        yield 'empty' => [
            [],
            [],
            'name',
            'new-name',
        ];
        yield 'equal-names' => [
            ['name' => 'asc', 'age' => 'desc'],
            ['name' => 'asc', 'age' => 'desc'],
            'name',
            'name',
        ];
    }

    #[DataProvider('dataReplaceFieldName')]
    public function testReplaceFieldName(array $expected, array $order, string $from, string $to): void
    {
        $result = OrderHelper::replaceFieldName($order, $from, $to);

        $this->assertSame($expected, $result);
    }
}
