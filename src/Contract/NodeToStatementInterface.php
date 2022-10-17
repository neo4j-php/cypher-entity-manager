<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\NodeInterface;

interface NodeToStatementInterface
{
    public static function nodeStatement(NodeInterface $node): Statement;
}
