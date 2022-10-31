<?php

declare(strict_types=1);

namespace Syndesi\CypherEntityManager\Contract;

use Laudis\Neo4j\Databags\Statement;
use Syndesi\CypherDataStructures\Contract\ConstraintInterface;

interface ConstraintStatementInterface
{
    public static function constraintStatement(ConstraintInterface $constraint): Statement;
}
