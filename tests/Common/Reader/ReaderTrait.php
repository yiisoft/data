<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;

trait ReaderTrait
{
    protected function getReader(): DataReaderInterface
    {
        return new IterableDataReader(self::$fixtures);
    }
}
