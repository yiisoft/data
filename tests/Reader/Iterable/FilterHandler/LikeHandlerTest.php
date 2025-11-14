<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\LikeMode;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\TestCase;

final class LikeHandlerTest extends TestCase
{
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

            [true, ['id' => 1, 'value' => 'das Öl'], 'value', 'öl', false],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item, string $field, string $value, ?bool $caseSensitive): void
    {
        $filterHandler = new LikeHandler();
        $context = new Context([], new FlatValueReader());
        $this->assertSame($expected, $filterHandler->match($item, new Like($field, $value, $caseSensitive), $context));
    }

    public static function matchWithModeDataProvider(): array
    {
        return [
            // "Contains" mode
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::Contains],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', false, LikeMode::Contains],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', true, LikeMode::Contains],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::Contains],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::Contains],

            // "StartsWith" mode
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'great', false, LikeMode::StartsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'great', true, LikeMode::StartsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::StartsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', 'Привет', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙁', null, LikeMode::StartsWith],

            // "EndsWith" mode
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'fighter', false, LikeMode::EndsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'fighter', true, LikeMode::EndsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::EndsWith],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat Fighter', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', 'мир', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙁', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'das Öl'], 'value', 'öl', false, LikeMode::EndsWith],

            // Edge cases
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::Contains],
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'test'], 'value', 'test', null, LikeMode::StartsWith],
            [true, ['id' => 1, 'value' => 'test'], 'value', 'test', null, LikeMode::EndsWith],
            [false, ['id' => 1, 'value' => 'test'], 'value', 'longer', null, LikeMode::StartsWith],
            [false, ['id' => 1, 'value' => 'test'], 'value', 'longer', null, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'Çağrı'], 'value', 'çağ', false, LikeMode::StartsWith],
            [false, ['id' => 1, 'value' => '🌟'], 'value', 'xyz🌟', false, LikeMode::EndsWith],
            [false, ['id' => 1, 'value' => 'é🎉'], 'value', 'abcé🎉', false, LikeMode::EndsWith],
            [true, ['id' => 1, 'value' => 'aliİ'], 'value', 'İ', false, LikeMode::EndsWith],
        ];
    }

    #[DataProvider('matchWithModeDataProvider')]
    public function testMatchWithMode(
        bool $expected,
        array $item,
        string $field,
        string $value,
        ?bool $caseSensitive,
        LikeMode $mode,
    ): void {
        $handler = new LikeHandler();
        $context = new Context([], new FlatValueReader());
        $filter = new Like($field, $value, $caseSensitive, $mode);

        $this->assertSame(
            $expected,
            $handler->match($item, $filter, $context),
        );
    }

    public function testConstructorDefaultMode(): void
    {
        $handler = new LikeHandler();
        $context = new Context([], new FlatValueReader());
        $item = ['id' => 1, 'value' => 'Great Cat Fighter'];

        $this->assertTrue($handler->match($item, new Like('value', 'Cat'), $context));
        $this->assertFalse($handler->match($item, new Like('value', 'Hello'), $context));
    }
}
