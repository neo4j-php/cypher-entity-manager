<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\IndexInterface;

interface IndexStatementInterface
{
    public static function indexStatement(IndexInterface $index): Statement;
}
