<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use RuntimeException;

/**
 * Exception occurred during reading data.
 *
 * @see DataReaderInterface
 *
 * @psalm-suppress ClassMustBeFinal We assume that the class may be extended in userland.
 */
class DataReaderException extends RuntimeException
{
}
