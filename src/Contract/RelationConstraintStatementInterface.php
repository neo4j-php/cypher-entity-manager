<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\RelationConstraintInterface;

interface RelationConstraintStatementInterface
{
    public static function relationConstraintStatement(RelationConstraintInterface $relationConstraint): Statement;
}
