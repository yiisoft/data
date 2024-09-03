<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithGreaterThanOrEqualTestCase;

final class ReaderWithGreaterThanOrEqualTest extends BaseReaderWithGreaterThanOrEqualTestCase
{
    use ReaderTrait;
}
