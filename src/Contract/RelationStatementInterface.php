<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\RelationInterface;

interface RelationStatementInterface
{
    public static function relationStatement(RelationInterface $relation): Statement;
}
