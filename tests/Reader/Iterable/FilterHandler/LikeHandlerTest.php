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

            // STARTS_WITH mode
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

            // Unicode test cases to catch mutants that replace mb_* functions
            // Test case for mb_stripos vs stripos in STARTS_WITH (catches mutant 1)
            // stripos would return false, mb_stripos returns 0 for Turkish Ç/ç
            [true, ['id' => 1, 'value' => 'Çağrı'], 'value', 'çağ', false, LikeMode::STARTS_WITH],

            // Test case for mb_strlen vs strlen in ENDS_WITH (attempts to catch mutant on line 74)
            // This tests the critical edge case where mb_strlen and strlen differ significantly
            // itemValue = '🌟' (1 char, 4 bytes), searchValue = 'xyz🌟' (4 chars, 7 bytes)
            // Original: mb_strlen('xyz🌟') > mb_strlen('🌟') → 4 > 1 → returns false (correct)
            // Mutant: mb_strlen('xyz🌟') > strlen('🌟') → 4 > 4 → false, proceeds to comparison
            // The mutant incorrectly proceeds when it should return false early
            [false, ['id' => 1, 'value' => '🌟'], 'value', 'xyz🌟', false, LikeMode::ENDS_WITH],

            // Additional test case for the same mutant with different multi-byte scenario
            // itemValue = 'é🎉' (2 chars, 6 bytes), searchValue = 'abcé🎉' (5 chars, 9 bytes)
            // Original: 5 > 2 → returns false, Mutant: 5 > 6 → false, proceeds to comparison
            [false, ['id' => 1, 'value' => 'é🎉'], 'value', 'abcé🎉', false, LikeMode::ENDS_WITH],

            // Test case for mb_strtolower vs strtolower in ENDS_WITH (catches mutant on line 78)
            // Use Turkish İ which strtolower doesn't handle properly
            // itemValue ends with Turkish İ, searchValue is also Turkish İ
            // mb_strtolower('İ') = 'i̇', strtolower('İ') = 'İ' (unchanged)
            // Original: 'i̇' === 'i̇' = true, Mutant: 'i̇' === 'İ' = false
            [true, ['id' => 1, 'value' => 'aliİ'], 'value', 'İ', false, LikeMode::ENDS_WITH],

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

    public function testConstructorDefaultMode(): void
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
