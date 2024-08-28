<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\LikeHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Common\Reader\ReaderTrait;
use Yiisoft\Data\Tests\TestCase;

final class LikeHandlerTest extends TestCase
{
    use FixtureTrait;
    use LikeHandlerWithReaderTestTrait;
    use ReaderTrait;

    public static function matchDataProvider(): array
    {
        return [
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat Fighter', null],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', null],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'id', '1', null],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙂', null],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', ' ', null],

            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat Fighter', false],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', false],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', false],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'id', '1', false],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙂', false],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', ' ', false],

            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat Fighter', true],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', true],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', true],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'id', '1', true],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙂', true],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', ' ', true],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item, string $field, string $value, ?bool $caseSensitive): void
    {
        $processor = new LikeHandler();
        $this->assertSame($expected, $processor->match($item, new Like($field, $value, $caseSensitive), []));
    }
}
