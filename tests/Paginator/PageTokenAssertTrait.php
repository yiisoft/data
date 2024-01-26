<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Paginator;

use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Paginator\PageToken;

/**
 * @psalm-require-extends TestCase
 */
trait PageTokenAssertTrait
{
    public function assertPageToken(
        string $expectedValue,
        bool $expectedIsPrevious,
        mixed $pageToken,
        string $message = ''
    ): void {
        static::assertThat($pageToken, new IsInstanceOf(PageToken::class), $message);
        static::assertThat($pageToken->value, new IsIdentical($expectedValue), $message);
        static::assertThat($pageToken->isPrevious, new IsIdentical($expectedIsPrevious), $message);
    }
}
