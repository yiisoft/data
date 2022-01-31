<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests;

use ReflectionObject;
use Traversable;

use function iterator_to_array;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Gets an inaccessible object property.
     *
     * @param object $object
     * @param string $propertyName
     * @param bool $revoke whether to make property inaccessible after getting
     *
     * @return mixed
     */
    protected function getInaccessibleProperty(object $object, string $propertyName, bool $revoke = true)
    {
        $class = new ReflectionObject($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);

        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
    }

    protected function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array)$iterable;
    }
}
