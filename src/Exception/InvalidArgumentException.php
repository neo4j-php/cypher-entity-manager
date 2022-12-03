<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Exception;

class InvalidArgumentException extends \Exception
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

    public static function createForStartNodeIsNull(): self
    {
        return new InvalidArgumentException("Start node of relation can not be null");
    }

    public static function createForEndNodeIsNull(): self
    {
        return new InvalidArgumentException("End node of relation can not be null");
    }

    public static function createForRelationTypeIsNull(): self
    {
        return new InvalidArgumentException("Relation type of relation can not be null");
    }

    public static function createForIndexNameIsNull(): self
    {
        return new InvalidArgumentException("Index name can not be null");
    }

    public static function createForIndexTypeIsNull(): self
    {
        return new InvalidArgumentException("Index type can not be null");
    }

    public static function createForIndexForIsNull(): self
    {
        return new InvalidArgumentException("Index for (node label / relation type) can not be null");
    }

    public static function createForConstraintTypeIsNull(): self
    {
        return new InvalidArgumentException("Constraint type can not be null");
    }

    public static function createForConstraintNameIsNull(): self
    {
        return new InvalidArgumentException("Constraint name can not be null");
    }

    public static function createForConstraintForIsNull(): self
    {
        return new InvalidArgumentException("Constraint for (node label / relation type) can not be null");
    }
}
