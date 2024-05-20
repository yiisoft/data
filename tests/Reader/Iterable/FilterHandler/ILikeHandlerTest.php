<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\ILike;
use Yiisoft\Data\Reader\Iterable\FilterHandler\ILikeHandler;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\Common\Reader\FilterHandler\ILikeHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\TestCase;

final class ILikeHandlerTest extends TestCase
{
    use ILikeHandlerWithReaderTestTrait;
    use FixtureTrait;

    public static function matchDataProvider(): array
    {
        return [
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat Fighter'],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat'],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat'],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'id', '1'],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙂'],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', ' '],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item, string $field, string $value): void
    {
        $processor = new ILikeHandler();
        $this->assertSame($expected, $processor->match($item, new ILike($field, $value), []));
    }
}
