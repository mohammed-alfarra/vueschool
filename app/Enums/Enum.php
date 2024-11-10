<?php

namespace App\Enums;

use ReflectionClass;

abstract class Enum
{
    /**
     * Get the values of every constant in the enum.
     */
    final public static function getAll(): array
    {
        return array_values(static::toArray());
    }

    /**
     * Convert the enum to an associative array of constant names and values.
     */
    final public static function toArray(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    /**
     * Get the constant names of the enum.
     */
    final public static function getConst(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * Check if a given value is a valid value of the enum.
     *
     * @param  mixed  $value  The value to be checked.
     */
    final public static function isValid(mixed $value): bool
    {
        return in_array($value, static::toArray());
    }

    /**
     * Check if a given value is a valid constant of the enum.
     *
     * @param  mixed  $value  The value to be checked.
     */
    final public static function isValidConst(mixed $value): bool
    {
        return in_array($value, static::getConst());
    }

    /**
     * Get the value of a given constant of the enum.
     *
     * @param  string  $const  The constant name.
     * @return mixed|null The value of the constant, or null if the constant does not exist.
     */
    final public static function getValue(string $const): mixed
    {
        return static::toArray()[$const] ?? null;
    }
}
