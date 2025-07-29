<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterInterface;

use function is_scalar;
use function sprintf;

/**
 * `In` filter defines a criteria for ensuring field value matches one of the value provided.
 */
final class In implements FilterInterface
{
    /**
     * @param string $field Name of the field to compare.
     * @param bool[]|float[]|int[]|string[] $values Values to check against.
     */
    public function __construct(
        public readonly string $field,
        /** @var bool[]|float[]|int[]|string[] Values to check against. */
        public readonly array $values
    ) {
        $this->assertValues($values);
    }

    private function assertValues(array $values): void
    {
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The value should be scalar. "%s" is received.',
                        get_debug_type($value),
                    )
                );
            }
        }
    }
}
