<?php

declare(strict_types=1);

namespace Yiisoft\Data\Processor;

use RuntimeException;

/**
 * Exception occurred during data processing.
 *
 * @see DataProcessorInterface
 *
 * @psalm-suppress ClassMustBeFinal We assume that the class may be extended in userland.
 */
class DataProcessorException extends RuntimeException
{
}
