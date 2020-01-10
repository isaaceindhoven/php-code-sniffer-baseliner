<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\Util;

use RuntimeException;

use function gettype;
use function is_array;
use function is_bool;
use function is_int;
use function is_object;
use function is_string;
use function property_exists;
use function sprintf;

class PropertyAccessor
{
    public static function getBooleanProperty(object $object, string $propertyName, string $objectName): bool
    {
        $property = self::getProperty($object, $propertyName, $objectName);
        if (!is_bool($property)) {
            throw self::createInvalidPropertyTypeException($propertyName, $objectName, 'boolean', $property);
        }
        return $property;
    }

    public static function getIntegerProperty(object $object, string $propertyName, string $objectName): int
    {
        $property = self::getProperty($object, $propertyName, $objectName);
        if (!is_int($property)) {
            throw self::createInvalidPropertyTypeException($propertyName, $objectName, 'integer', $property);
        }
        return $property;
    }

    public static function getStringProperty(object $object, string $propertyName, string $objectName): string
    {
        $property = self::getProperty($object, $propertyName, $objectName);
        if (!is_string($property)) {
            throw self::createInvalidPropertyTypeException($propertyName, $objectName, 'string', $property);
        }
        return $property;
    }

    /**
     * @return mixed[]
     */
    public static function getArrayProperty(object $object, string $propertyName, string $objectName): array
    {
        $property = self::getProperty($object, $propertyName, $objectName);
        if (!is_array($property)) {
            throw self::createInvalidPropertyTypeException($propertyName, $objectName, 'array', $property);
        }
        return $property;
    }

    public static function getObjectProperty(object $object, string $propertyName, string $objectName): object
    {
        $property = self::getProperty($object, $propertyName, $objectName);
        if (!is_object($property)) {
            throw self::createInvalidPropertyTypeException($propertyName, $objectName, 'object', $property);
        }
        return $property;
    }

    /**
     * @return mixed
     */
    private static function getProperty(object $object, string $propertyName, string $objectName)
    {
        if (!property_exists($object, $propertyName)) {
            throw new RuntimeException(sprintf('Missing property \'%s\' in %s.', $propertyName, $objectName));
        }
        return $object->{$propertyName};
    }

    /**
     * @param mixed $property
     */
    private static function createInvalidPropertyTypeException(
        string $propertyName,
        string $objectName,
        string $expectedType,
        $property
    ): RuntimeException {
        return new RuntimeException(sprintf(
            'Expected property \'%s\' in %s to be an ' . $expectedType . ', got %s.',
            $propertyName,
            $objectName,
            gettype($property)
        ));
    }
}
