<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;

interface SimilarRelationQueueStatementInterface
{
    public static function similarRelationQueueStatement(SimilarRelationQueueInterface $similarRelationQueue): Statement;
}
