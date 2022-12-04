<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\HasIdentifiersInterface;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToStringHelper;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;

class StructureHelper
{
    public static function getEmptyStatement(): Statement
    {
        return Statement::create('MATCH (n) LIMIT 0');
    }

    /**
     * @param array<string, mixed> $identifiers
     */
    public static function identifiersToStructure(array $identifiers): string
    {
        $internalIdentifiers = [];
        $publicIdentifiers = [];
        foreach ($identifiers as $key => $value) {
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
        if (0 === count($node->getIdentifiers())) {
            throw new InvalidArgumentException('at least one identifier is required');
        }
        $parts = [];
        $cypherLabelString = ToStringHelper::labelsToString($node->getLabels());
        if ('' !== $cypherLabelString) {
            $parts[] = $cypherLabelString;
        }
        $parts[] = self::identifiersToStructure($node->getIdentifiers());

        return '('.implode(' ', $parts).')';
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getRelationStructure(RelationInterface $relation): string
    {
        $startNode = $relation->getStartNode();
        if (!$startNode) {
            throw InvalidArgumentException::createForStartNodeIsNull();
        }
        $endNode = $relation->getEndNode();
        if (!$endNode) {
            throw InvalidArgumentException::createForEndNodeIsNull();
        }
        $type = $relation->getType();
        if (!$type) {
            throw InvalidArgumentException::createForRelationTypeIsNull();
        }
        if (0 === count($relation->getIdentifiers())) {
            throw new InvalidArgumentException('at least one relation identifier is required');
        }
        $parts = [];
        $parts[] = self::getNodeStructure($startNode);
        $parts[] = sprintf(
            "-[%s %s]->",
            $type,
            self::identifiersToStructure($relation->getIdentifiers())
        );
        $parts[] = self::getNodeStructure($endNode);

        return implode('', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPropertiesWhichAreNotIdentifiers(HasIdentifiersInterface $element): array
    {
        $properties = [];
        foreach ($element->getProperties() as $name => $value) {
            if ($element->hasIdentifier($name)) {
                continue;
            }
            $properties[$name] = $value;
        }

        return $properties;
    }

    /**
     * @param array<string, mixed> $properties
     */
    public static function getPropertiesAsCypherVariableString(array $properties, string $variablePrefix): string
    {
        $parts = [];
        foreach ($properties as $name => $value) {
            $parts[] = sprintf(
                "%s: %s.%s",
                $name,
                $variablePrefix,
                $name,
            );
        }

        return implode(', ', $parts);
    }
}
