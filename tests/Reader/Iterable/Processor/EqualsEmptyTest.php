<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\EqualsEmpty;

final class EqualsEmptyTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['value' => null]],
            [true, ['value' => false]],
            [true, ['value' => 0]],
            [true, ['value' => 0.0]],
            [true, ['value' => '']],
            [false, ['value' => 42]],
            [false, ['value' => true]],
            [true, ['not-exist' => 42]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsEmpty())->match($item, ['value'], []));
    }
}
