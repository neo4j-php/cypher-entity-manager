<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper\Statement;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;

class DeleteNodeStatement implements NodeStatementInterface
{
    public static function nodeStatement(NodeInterface $node): Statement
    {
        $identifyingStrings = [];
        $propertyValues = [];
        /** @var PropertyNameInterface $identifierName */
        foreach ($node->getIdentifiersWithPropertyValues() as $identifierName) {
            $identifyingStrings[] = sprintf(
                "%s: $%s",
                (string) $identifierName,
                (string) $identifierName
            );
            $propertyValues[(string) $identifierName] = $node->getProperty($identifierName);
        }
        $identifyingString = implode(", ", $identifyingStrings);

        return new Statement(
            sprintf(
                "MATCH (node%s {%s})\n".
                "DETACH DELETE node",
                ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
                $identifyingString
            ),
            $propertyValues
        );
    }
}
