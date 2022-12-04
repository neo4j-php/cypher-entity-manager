<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeConstraintInterface;

interface NodeConstraintStatementInterface
{
    public static function nodeConstraintStatement(NodeConstraintInterface $nodeConstraint): Statement;
}
