<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Equals;

trait AllHandlerWithReaderTestTrait
{
    public function testWithReader(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(new All(new Equals('balance', 100), new Equals('email', 'seed@beat')));
        $this->assertFixtures([2], $reader->read());
    }
}
