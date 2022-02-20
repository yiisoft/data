<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests;

use ReflectionException;
use ReflectionObject;
use stdClass;
use Traversable;

use function iterator_to_array;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function invalidArrayValueDataProvider(): array
    {
        return [
            'bool-true' => [true],
            'bool-false' => [false],
            'callback' => [fn () => null],
            'float' => [1.0],
            'int' => [1],
            'null' => [null],
            'string' => ['string'],
            'object' => [new stdClass()],
        ];
    }

    public function invalidStringValueDataProvider(): array
    {
        return [
            'array' => [[]],
            'bool-true' => [true],
            'bool-false' => [false],
            'callback' => [fn () => null],
            'float' => [1.0],
            'int' => [1],
            'null' => [null],
            'object' => [new stdClass()],
        ];
    }

    public function invalidFilterDataProvider(): array
    {
        return [
            'callback' => [fn () => null],
            'float' => [1.0],
            'int' => [1],
            'null' => [null],
            'string' => ['string'],
            'object' => [new stdClass()],
        ];
    }

    public function invalidFilterOperatorDataProvider(): array
    {
        return [
            'array' => [[[]]],
            'callback' => [[fn () => null]],
            'empty-string' => [['']],
            'float' => [[1.0]],
            'int' => [[1]],
            'null' => [[null]],
            'object' => [[new stdClass()]],
        ];
    }

    public function invalidScalarValueDataProvider(): array
    {
        return [
            'array' => [[]],
            'callback' => [fn () => null],
            'null' => [null],
            'object' => [new stdClass()],
        ];
    }

    /**
     * Gets an inaccessible object property.
     *
     * @return mixed
     */
    protected function getInaccessibleProperty(object $object, string $propertyName)
    {
        $class = new ReflectionObject($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        $property->setAccessible(false);

        return $result;
    }

    /**
     * Invokes an inaccessible method.
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    protected function invokeMethod(object $object, string $method, array $args = [])
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $args);
        $method->setAccessible(false);

        return $result;
    }

    protected function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array) $iterable;
    }
}
