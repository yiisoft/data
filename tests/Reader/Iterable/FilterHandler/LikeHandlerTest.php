<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Tests\Common\LikeHandlerWithReaderTestTrait;
use Yiisoft\Data\Tests\Common\ReaderTestTrait;
use Yiisoft\Data\Tests\TestCase;

final class LikeHandlerTest extends TestCase
{
    use ReaderTestTrait;
    use LikeHandlerWithReaderTestTrait;

    public static function matchDataProvider(): array
    {
        return [
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Great Cat Fighter'],
            [true, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'Cat'],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'value', 'cat'],
            [false, ['id' => 1, 'value' => 'Great Cat Fighter'], 'id', '1'],
            [true, ['id' => 1, 'value' => '🙁🙂🙁'], 'value', '🙂'],
            [true, ['id' => 1, 'value' => 'Привет мир'], 'value', ' '],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item, string $field, string $value): void
    {
        $processor = new LikeHandler();
        $this->assertSame($expected, $processor->match($item, new Like($field, $value), []));
    }
}
