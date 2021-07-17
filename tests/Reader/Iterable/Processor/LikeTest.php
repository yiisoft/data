<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\Like;

final class LikeTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['name', 'Great Cat Fighter']],
            [true, ['name', 'Cat']],
            [false, ['id', '1']],
            [false, ['nick', 'Fighter42']],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $arguments): void
    {
        $processor = new Like();

        $item = [
            'id' => 1,
            'name' => 'Great Cat Fighter',
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }
}
