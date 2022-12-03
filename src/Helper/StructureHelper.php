<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\HasIdentifiersInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Contract\PropertyStorageInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;

/**
 * todo fix.
 */
class StructureHelper
{
    public static function getEmptyStatement(): Statement
    {
        return Statement::create('MATCH (n) LIMIT 0');
    }

    public static function identifierStorageToString(PropertyStorageInterface $identifiers): string
    {
        $internalIdentifiers = [];
        $publicIdentifiers = [];
        foreach ($identifiers as $key) {
            $key = (string) $key;
            if (str_starts_with($key, '_')) {
                $internalIdentifiers[] = $key;
            } else {
                $publicIdentifiers[] = $key;
            }
        }
        sort($internalIdentifiers);
        sort($publicIdentifiers);
        $identifierStringParts = array_merge($internalIdentifiers, $publicIdentifiers);

        return implode(', ', $identifierStringParts);
    }

    public static function getNodeStructure(NodeInterface $node): string
    {
        if (0 === $node->getIdentifiers()->count()) {
            throw new InvalidArgumentException('at least one identifier is required');
        }
        $parts = [];
        $cypherLabelString = ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels());
        if ('' !== $cypherLabelString) {
            $parts[] = $cypherLabelString;
        }
        $parts[] = self::identifierStorageToString($node->getIdentifiers());

        return '('.implode(' ', $parts).')';
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getRelationStructure(RelationInterface $relation): string
    {
        if (null === $relation->getStartNode()) {
            throw new InvalidArgumentException('start node can not be null');
        }
        if (null === $relation->getEndNode()) {
            throw new InvalidArgumentException('end node can not be null');
        }
        if (0 === $relation->getIdentifiers()->count()) {
            throw new InvalidArgumentException('at least one relation identifier is required');
        }
        $parts = [];
        /** @psalm-suppress PossiblyNullArgument */
        $parts[] = self::getNodeStructure($relation->getStartNode());
        /** @psalm-suppress PossiblyNullArgument */
        $parts[] = sprintf(
            "-[%s %s]->",
            (string) $relation->getRelationType(),
            self::identifierStorageToString($relation->getIdentifiers())
        );
        /** @psalm-suppress PossiblyNullArgument */
        $parts[] = self::getNodeStructure($relation->getEndNode());

        return implode('', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getIdentifiersFromElementAsArray(HasIdentifiersInterface $element): array
    {
        $identifiers = [];
        /** @var PropertyNameInterface $identifier */
        foreach ($element->getIdentifiers() as $identifier) {
            $identifiers[$identifier->getPropertyName()] = $element->getIdentifier($identifier);
        }

        return $identifiers;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPropertiesFromElementAsArray(HasIdentifiersInterface $element): array
    {
        $properties = [];
        /** @var PropertyNameInterface $property */
        foreach ($element->getProperties() as $property) {
            if ($element->hasIdentifier($property)) {
                continue;
            }
            $properties[$property->getPropertyName()] = $element->getProperty($property);
        }

        return $properties;
    }

    public static function getIdentifiersFromElementAsCypherVariableString(HasIdentifiersInterface $element, string $variablePrefix): string
    {
        $identifiers = [];
        /** @var PropertyNameInterface $identifier */
        foreach ($element->getIdentifiers() as $identifier) {
            $identifiers[] = sprintf(
                "%s: %s.%s",
                $identifier->getPropertyName(),
                $variablePrefix,
                $identifier->getPropertyName(),
            );
        }

        return implode(', ', $identifiers);
    }
}
