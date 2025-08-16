<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\AllHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

final class AllHandlerTest extends TestCase
{
    public function testMatchAlwaysTrue(): void
    {
        $handler = new AllHandler();
        $context = new Context([], new FlatValueReader());

        $result = $handler->match(['any' => 'value'], new All(), $context);

        assertTrue($result);
    }

    public function testGetFilterClass(): void
    {
        $handler = new AllHandler();
        assertSame(All::class, $handler->getFilterClass());
    }
}
