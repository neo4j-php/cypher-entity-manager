<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;

interface SimilarNodeQueueStatementInterface
{
    public static function similarNodeQueueStatement(SimilarNodeQueueInterface $similarNodeQueue): Statement;
}
