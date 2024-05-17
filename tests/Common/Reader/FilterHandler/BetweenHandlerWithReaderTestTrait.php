<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\Between;

trait BetweenHandlerWithReaderTestTrait
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new Between('balance', 10.25, 100.0));
        $this->assertFixtures([0, 2, 4], $reader->read());
    }
}
