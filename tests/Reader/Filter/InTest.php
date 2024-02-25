<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use stdClass;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Tests\TestCase;

final class InTest extends TestCase
{
    public function testNotScalarValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value should be scalar. "' . stdClass::class . '" is received.');
        new In('test', [new stdClass()]);
    }
}
