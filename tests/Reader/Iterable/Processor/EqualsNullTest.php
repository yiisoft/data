<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\EqualsNull;

final class EqualsNullTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['value' => null]],
            [false, ['value' => false]],
            [false, ['value' => true]],
            [false, ['value' => 0]],
            [false, ['value' => 0.0]],
            [false, ['value' => 42]],
            [false, ['value' => '']],
            [false, ['value' => 'null']],
            [false, ['not-exist' => null]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsNull())->match($item, ['value'], []));
    }
}
