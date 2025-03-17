<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\TestCase;

final class LikeTest extends TestCase
{
    public function testBase(): void
    {
        $like = new Like('name', 'Kesha');

        $this->assertSame('name', $like->getField());
        $this->assertSame('Kesha', $like->getValue());
        $this->assertNull($like->getCaseSensitive());
    }

    public function testWithCaseSensitive(): void
    {
        $like = new Like('name', 'Kesha', true);

        $this->assertSame('name', $like->getField());
        $this->assertSame('Kesha', $like->getValue());
        $this->assertTrue($like->getCaseSensitive());
    }
}
