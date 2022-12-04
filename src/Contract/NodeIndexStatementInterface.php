<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeIndexInterface;

interface NodeIndexStatementInterface
{
    public static function nodeIndexStatement(NodeIndexInterface $nodeIndex): Statement;
}
