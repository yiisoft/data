<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\TestCase;

abstract class BaseReaderTestCase extends TestCase
{
    use FixtureTrait;

    abstract protected function getReader(): DataReaderInterface;
}
