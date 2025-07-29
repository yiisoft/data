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
            // CONTAINS mode
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::CONTAINS],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', false, LikeMode::CONTAINS],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat', true, LikeMode::CONTAINS],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::CONTAINS],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::CONTAINS],

            // STARTS_WITH mode tests
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'great', false, LikeMode::STARTS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'great', true, LikeMode::STARTS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::STARTS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', 'Привет', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙁', null, LikeMode::STARTS_WITH],

            // ENDS_WITH mode
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Fighter', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'fighter', false, LikeMode::ENDS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'fighter', true, LikeMode::ENDS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great', null, LikeMode::ENDS_WITH],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat Fighter', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', 'мир', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙁', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => 'das Öl'], 'value', 'öl', false, LikeMode::ENDS_WITH],

            // Edge cases
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::CONTAINS],
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => 'test'], 'value', '', null, LikeMode::ENDS_WITH],
            [true, ['id' => 1, 'value' => 'test'], 'value', 'test', null, LikeMode::STARTS_WITH],
            [true, ['id' => 1, 'value' => 'test'], 'value', 'test', null, LikeMode::ENDS_WITH],
            [false, ['id' => 1, 'value' => 'test'], 'value', 'longer', null, LikeMode::STARTS_WITH],
            [false, ['id' => 1, 'value' => 'test'], 'value', 'longer', null, LikeMode::ENDS_WITH],
        ];
    }

    #[DataProvider('matchWithModeDataProvider')]
    public function testMatchWithMode(
        bool $expected,
        array $item,
        string $field,
        string $value,
        ?bool $caseSensitive,
        LikeMode $mode
    ): void {
        $filterHandler = new LikeHandler();
        $context = new Context([], new FlatValueReader());
        $this->assertSame(
            $expected,
            $filterHandler->match($item, new Like($field, $value, $caseSensitive, $mode), $context)
        );
    }

    public function testBackwardCompatibility(): void
    {
        $filterHandler = new LikeHandler();
        $context = new Context([], new FlatValueReader());
        $item = ['id' => 1, 'value' => 'Great Cat Fighter'];

        // Test that constructor defaults to CONTAINS mode
        $oldFilter = new Like('value', 'Cat');
        $newFilter = new Like('value', 'Cat', null, LikeMode::CONTAINS);

        $this->assertSame(
            $filterHandler->match($item, $oldFilter, $context),
            $filterHandler->match($item, $newFilter, $context)
        );
    }
}
