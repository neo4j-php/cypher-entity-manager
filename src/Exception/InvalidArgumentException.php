<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Exception;

use Exception;

class InvalidArgumentException extends Exception
{
    public static function createForNotSimilar(string $type, string $similarExpected, string $similarGot): self
    {
        return new InvalidArgumentException(sprintf(
            "Expected type '%s' with similar structure of '%s', got '%s'",
            $type,
            $similarExpected,
            $similarGot
        ));
    }

    public static function createForTypeMismatch(string $typeExpected, string $typeGot): self
    {
        return new InvalidArgumentException(sprintf(
            "Expected type '%s', got type '%s'",
            $typeExpected,
            $typeGot
        ));
    }

    public static function createForNotCypherElementType(string $typeGot): self
    {
        return self::createForTypeMismatch('NodeInterface|RelationInterface|IndexInterface|ConstraintInterface', $typeGot);
    }
}
