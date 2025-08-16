<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Filter\None;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NoneHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;

use function PHPUnit\Framework\assertFalse;

final class NoneHandlerTest extends TestCase
{
    public function testMatchAlwaysFalse(): void
    {
        $handler = new NoneHandler();
        $context = new Context([], new FlatValueReader());

        $result = $handler->match(['any' => 'value'], new None(), $context);

        assertFalse($result);
    }

    public function testGetFilterClass(): void
    {
        $handler = new NoneHandler();
        $this->assertSame(None::class, $handler->getFilterClass());
    }
}
