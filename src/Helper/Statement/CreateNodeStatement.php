<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Helper\Statement;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeInterface;
use Syndesi\CypherDataStructures\Contract\PropertyNameInterface;
use Syndesi\CypherDataStructures\Helper\ToCypherHelper;
use Syndesi\CypherEntityManager\Contract\NodeStatementInterface;

class CreateNodeStatement implements NodeStatementInterface
{
    public static function nodeStatement(NodeInterface $node): Statement
    {
        $propertyString = [];
        $propertyValues = [];
        /** @var PropertyNameInterface $propertyName */
        foreach ($node->getProperties() as $propertyName) {
            $propertyString[] = sprintf(
                "%s: $%s",
                (string) $propertyName,
                (string) $propertyName
            );
            $propertyValues[(string) $propertyName] = $node->getProperty($propertyName);
        }

        return new Statement(
            sprintf(
            "CREATE (%s {%s})",
            ToCypherHelper::nodeLabelStorageToCypherLabelString($node->getNodeLabels()),
            implode(', ', $propertyString)
        ),
            $propertyValues
        );
    }
}
