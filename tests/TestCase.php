<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests;

use DateTimeImmutable;
use DateTimeInterface;
use ReflectionObject;
use stdClass;
use Traversable;

use function iterator_to_array;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function invalidArrayValueDataProvider(): array
    {
        return [
            'bool-true' => [true],
            'bool-false' => [false],
            'callback' => [fn() => null],
            'float' => [1.0],
            'int' => [1],
            'null' => [null],
            'string' => ['string'],
            'object' => [new stdClass()],
        ];
    }

    public static function invalidFilterDataProvider(): array
    {
        return [
            'callback' => [fn() => null],
            'float' => [1.0],
            'int' => [1],
            'null' => [null],
            'string' => ['string'],
            'object' => [new stdClass()],
        ];
    }

    public static function invalidFilterOperatorDataProvider(): array
    {
        return [
            'array' => [[[]]],
            'callback' => [[fn() => null]],
            'empty-string' => [['']],
            'float' => [[1.0]],
            'int' => [[1]],
            'null' => [[null]],
            'object' => [[new stdClass()]],
        ];
    }

    public static function invalidScalarValueDataProvider(): array
    {
        return [
            'array' => [[]],
            'callback' => [fn() => null],
            'null' => [null],
            'object' => [new stdClass()],
        ];
    }

    public static function scalarAndDataTimeInterfaceValueDataProvider(): array
    {
        return [
            'bool-true' => [true],
            'bool-false' => [false],
            'float' => [1.1],
            'int' => [1],
            'string' => [''],
            DateTimeInterface::class => [new DateTimeImmutable()],
        ];
    }

    /**
     * Gets an inaccessible object property.
     */
    protected function getInaccessibleProperty(object $object, string $propertyName): mixed
    {
        $class = new ReflectionObject($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        return $class
            ->getProperty($propertyName)
            ->getValue($object);
    }

    /**
     * Invokes an inaccessible method.
     */
    protected function invokeMethod(object $object, string $method, array $args = []): mixed
    {
        return (new ReflectionObject($object))
            ->getMethod($method)
            ->invokeArgs($object, $args);
    }

    protected function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array) $iterable;
    }
}
