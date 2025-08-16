<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\None;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

use function PHPUnit\Framework\assertSame;

abstract class BaseReaderWithNoneTestCase extends BaseReaderTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new None());

        $result = $reader->read();

        assertSame([], $result);
    }
}
