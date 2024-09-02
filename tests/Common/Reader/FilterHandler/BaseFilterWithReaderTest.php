<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\FilterHandler;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Tests\Common\FixtureTrait;
use Yiisoft\Data\Tests\TestCase;

abstract class BaseFilterWithReaderTest extends TestCase
{
    use FixtureTrait;

    protected function getReader(): DataReaderInterface
    {
        return new IterableDataReader(self::$fixtures);
    }
}
