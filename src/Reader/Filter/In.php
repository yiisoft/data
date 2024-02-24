<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterInterface;

/**
 * `In` filter defines a criteria for ensuring field value matches one of the value provided.
 */
final class In implements FilterInterface
{
    /**
     * @var bool[]|float[]|int[]|string[] Values to check against.
     */
    private array $values;

    /**
     * @param string $field Name of the field to compare.
     * @param bool[]|float[]|int[]|string[] $values Values to check against.
     */
    public function __construct(
        public readonly string $field,
        array $values
    ) {
        foreach ($values as $value) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The value should be scalar. "%s" is received.',
                        get_debug_type($value),
                    )
                );
            }
        }
        $this->values = $values;
    }

    /**
     * @return bool[]|float[]|int[]|string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
