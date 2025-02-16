<?php

declare(strict_types=1);

namespace Yiisoft\Data\Writer;

use RuntimeException;

/**
 * Exception occurred during writing or deleting data.
 *
 * @see DataWriterInterface
 *
 * @psalm-suppress ClassMustBeFinal We assume that the class may be extended in userland.
 */
class DataWriterException extends RuntimeException
{
}
