<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper;

use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyStorageInterface;
use Syndesi\CypherDataStructures\Contract\RelationInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Exception\InvalidArgumentException;

class StructureHelper
{
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
            throw new InvalidArgumentException('can not be null');
        }
        if (null === $relation->getEndNode()) {
            throw new InvalidArgumentException('can not be null');
        }
        if (0 === $relation->getIdentifiers()->count()) {
            throw new InvalidArgumentException('at least one identifier is required');
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
}
