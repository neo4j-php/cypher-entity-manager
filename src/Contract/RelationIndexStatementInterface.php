<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\RelationIndexInterface;

interface RelationIndexStatementInterface
{
    public static function relationIndexStatement(RelationIndexInterface $relationIndex): Statement;
}
